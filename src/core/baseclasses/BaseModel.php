<?php

namespace Robot\Core\BaseClasses;

use Exception;
use PDO;
use PDOException;
use Robot\Core\Contracts\IModel;
use Robot\Core\Database\DataRow;
use Robot\Core\Database\DataSet;
use Robot\Core\Database\DB;

/**
 * @method $this where()
 *
 */

class BaseModel implements IModel
{
    private static $connection;
    private $join_fields = [];
    private $join_tables = [];
    protected static $table;
    protected static $primary_key;
    protected static $where = '';
    protected static $glueWhere = ' and ';
    protected static $join = '';
    protected static $join_columns = '';
    protected static $order = '';
    protected static $query;

    function __construct(...$args)
    {
        self::$connection = DB::initialize();

    }

    function __call($name, $arguments)
    {
        $method_map = [
            'where' => 'dynamicWhere'
        ];

        if(array_key_exists($name,$method_map))
            return call_user_func([$this,$method_map[$name]],$arguments);

        throw new Exception("Method $name not found in " . static::class);
    }

    private static function generateWhereForArray($field, $values, $operator = 'or')
    {
        $where_clause = '(';
        $first = true;
        foreach ($values as $value)
        {
            if(!$first)
                $where_clause .= ' ' . $operator;
            else
                $first = false;

            $where_clause .= "`$field`" . '=' . $value;
        }
        $where_clause .= ')';
        return $where_clause;
    }

    private static function prepareWhere($column, $operator = '=', $value = null, $boolean = ' and ')
    {
        if(is_array($column))
        {
            foreach ($column as $condition)
            {
                if(count($condition)==2)
                    static::$where .= (empty(static::$where)?'':' and (') . "`$condition[0]`" . self::sanitizeValues($condition[1])  . ')';
                else if(count($condition)==3)
                    static::$where .= (empty(static::$where)?'':' and (') . "`$condition[0]`" . $condition[1] . self::sanitizeValues($condition[2]) . ')';
            }
            return new static();
        }
        if(count(func_get_args()==2))
        {
            static::$where .= (empty(static::$where)?'':static::$glueWhere) . "(`$column`" . '=' . self::sanitizeValues($operator) . ')';
        }
        else
        {
            static::$where .= (empty(static::$where)?'':static::$glueWhere) . "(`$column`" . $operator . self::sanitizeValues($value) . ')';
            static::$glueWhere = $boolean;
        }
    }

    private static function sanitizeValues($input)
    {

        return (is_string($input)? str_replace("\\","\\\\", "'$input'"):$input);

    }

    private static function sanitizeFields($input)
    {
        if(strpos($input,'.'))
        {
            $parts = explode('.',$input);
            $input = static::sanitizeFields($parts[0]) . '.' . static::sanitizeFields($parts[1]);
            return $input;
        }
        return "`$input`";
    }


    /**
     * @param $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return static
     */
    protected function dynamicWhere($column, $operator = '=', $value = null, $boolean = ' and ')
    {
        self::prepareWhere($column,$operator,$value,$boolean);
        return $this;
    }

    protected function hasMany($table,$primary_key,$foreign_key,$join='LEFT')
    {
        $db_name = env('database_name');
        $join_columns = [];
        $this->join_tables[] = $table;
        DB::selectEach('information_schema.COLUMNS',function($row)use($table,&$join_columns){

            $join_columns[] = $table . '.' . $row['COLUMN_NAME'] . ' AS ' . $table . '__' . $row['COLUMN_NAME'];

        },['COLUMN_NAME'],"`TABLE_SCHEMA` = '$db_name' AND `TABLE_NAME`='$table'");

        $this->join_fields = array_merge($this->join_fields,$join_columns);

        $destination_table = DB::sanitizeFields($table);
        $primary_key = /*$source_table . '.' . */self::sanitizeFields($primary_key);
        $foreign_key = /*$destination_table . '.' .*/ self::sanitizeFields($foreign_key);
        static::$join .= " $join JOIN $destination_table ON $primary_key=$foreign_key";

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     * @throws Exception
     */
    public static function all()
    {
        return new static();
    }


    /**
     * @param $ids
     * @return static
     */
    public static function find($ids)
    {
        if(!is_array($ids))
            $ids = [$ids];

        static::$where = self::generateWhereForArray(static::$primary_key,$ids);

        return new static();
    }

    /**
     * @param $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return static
     */
    public static function where($column, $operator = '=', $value = null, $boolean = ' and ')
    {
        self::prepareWhere($column,$operator,$value,$boolean);
        return new static();

    }

    public static function raw($sql,$bindings = [])
    {
        DB::raw($sql);
    }

    public function orderBy($orders = ['id','ASC'])
    {
        static::$order = ' ORDER BY ';
        if(count($orders)%2!=0)
            $orders[] = 'ASC';

        for($i=0;$i<count($orders);$i+=2)
        {
            static::$order .= static::sanitizeFields($orders[$i]) . ' ' . $orders[$i+1] . ',';
        }
        static::$order = rtrim(static::$order,',');
        return $this;
    }

    public function get($columns = ['*'])
    {
        if (count($columns) == 1 && $columns[0]=='*')
        {
            unset($columns[0]);
            $columns = is_array(static::$primary_key)?static::$primary_key:[static::$primary_key];
            $table = static::$table;
            $db_name = env('database_name');
            DB::selectEach('information_schema.COLUMNS',function($row)use($table,&$columns){
                if(!in_array($row['COLUMN_NAME'],$columns))
                    $columns[] = $table . '.' . $row['COLUMN_NAME'];

            },['COLUMN_NAME'],"`TABLE_SCHEMA` = '$db_name' AND `TABLE_NAME`='$table'");

//            $columns[0] = self::sanitizeFields(static::$table) . '.*';
        }
        else
        {
            if(is_array(static::$primary_key))
            {
                foreach (static::$primary_key as $key) {
                    if(!in_array($key,$columns))
                        $columns[]= static::$table . '.' . $key;
                }
            }
            else
            {
                if(!in_array(static::$primary_key,$columns))
                    $columns[]= static::$table . '.' . static::$primary_key;
            }
        }

        $columns = array_merge($columns,$this->join_fields);

        $ds = new DataSet();
        $table = static::$table;

        DB::selectEach(static::$table,function($row)use(&$ds , $table){
            $dr = new DataRow($table,$row,self::bridge($row));

            foreach($row as $field => $value)
            {
                $join_data  = explode('__',$field);

                if(count($join_data)>1)
                {
                    $join_table = $join_data[0];
                    $join_field = $join_data[1];

                    $join_data_row = $dr->addRowGroup($join_table,$table);
                    $join_data_row->$join_field = $value;

                }
                else
                {
                    $dr->$field = $value;
                }
            }
            $ds->add($dr);

        }, $columns,static::$where,static::$join,static::$order);

        return $ds;
    }

    public function getTableName()
    {
        return static::$table;
    }

    public static function create($attributes)
    {
        $id = DB::insert(static::$table,$attributes);
        if($id!=0)
            return new DataRow(static::$table,$attributes,[static::$primary_key=>$id]);

        return new DataRow(static::$table,$attributes,self::bridge($attributes));

    }

    private static function bridge($attr)
    {
        if(empty(static::$primary_key))
            return false;

        if(is_array(static::$primary_key))
        {

            return self::getPairsByKeys($attr,static::$primary_key);
/*            $where = '';
            foreach (static::$primary_key as $key_name)
                $where .= $key_name . '=' . $attr[$key_name] . ' AND ';

            $where = rtrim($where,' AND ');
            $u = DB::select(static::$table, ['*'] ,$where);
            if(count($u)!=1)
                return false;

            $t=[];
            foreach (static::$primary_key as $key_name)
                $t[$key_name] = $u[0][$key_name];

            return $t;*/
        }

        return [static::$primary_key => $attr[static::$primary_key]];
        /*$u = DB::select(static::$table, [static::$primary_key],static::$primary_key . '=' .$attr[static::$primary_key]);
        if(count($u)!=1)
            return false;

        return [static::$primary_key => $u[0][static::$primary_key]];*/
    }

    /**
     * @param $attributes
     * @return array of DataRow
     * @throws \Robot\Core\Database\Exception
     */
    public static function update($attributes)
    {
        $updated_rows = DB::update(static::$table,$attributes,static::$where);
        $result = [];
        foreach ($updated_rows as $row)
        {
            $result[] = new DataRow(static::$table,$row,self::bridge($row));
        }
        return $result;
    }

    private static function getPairsByKeys($array_pairs,$array_keys)
    {
        $result = [];
        foreach ($array_keys as $array_key) {
            if(isset($array_pairs[$array_key]))
                $result[$array_key] = $array_pairs[$array_key];
        }
        return $result;
    }
}