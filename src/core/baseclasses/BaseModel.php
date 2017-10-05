<?php

namespace Robot\Core\BaseClasses;

use Exception;
use PDO;
use PDOException;
use Robot\Core\Contracts\IModel;
use Robot\Core\Database\DataRow;
use Robot\Core\Database\DB;

/**
 * @method $this where()
 *
 */

class BaseModel implements IModel
{
    private static $connection;
    protected static $table;
    protected static $where = '';
    protected static $glueWhere = ' and ';
    protected static $join = '';
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
        $destination_table = self::sanitizeFields($table);

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

        static::$where = self::generateWhereForArray('id',$ids);

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
        return DB::select(static::$table,$columns,static::$where,static::$join,static::$order);
    }

    public function getTableName()
    {
        return static::$table;
    }

    public static function create($attributes)
    {
        $id = DB::insert(static::$table,$attributes);
        return new DataRow(new static(),$id);
    }

    /**
     * @param $attributes
     * @return array of DataRow
     * @throws \Robot\Core\Database\Exception
     */
    public static function update($attributes)
    {
        $updated_ids = DB::update(static::$table,$attributes,static::$where);;
        $result = [];
        foreach ($updated_ids as $id)
        {
            $result[] = new DataRow(new static(),$id);
        }
        return $result;
    }
}