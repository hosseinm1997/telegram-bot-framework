<?php

namespace Robot\Core\BaseClasses;

use Exception;
use PDO;
use PDOException;
use Robot\Core\Contracts\IModel;
use Robot\Core\Database\DB;

/**
 * @method $this where()
 *
 */

class BaseModel extends DB implements IModel
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
        self::$connection = parent::getInstance();

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
                    static::$where .= (empty(static::$where)?'':' and (') . "`$condition[0]`" . self::sanitizeSql($condition[1])  . ')';
                else if(count($condition)==3)
                    static::$where .= (empty(static::$where)?'':' and (') . "`$condition[0]`" . $condition[1] . self::sanitizeSql($condition[2]) . ')';
            }
            return new static();
        }
        if(count(func_get_args()==2))
        {
            static::$where .= (empty(static::$where)?'':static::$glueWhere) . "(`$column`" . '=' . self::sanitizeSql($operator) . ')';
        }
        else
        {
            static::$where .= (empty(static::$where)?'':static::$glueWhere) . "(`$column`" . $operator . self::sanitizeSql($value) . ')';
            static::$glueWhere = $boolean;
        }
    }

    private static function sanitizeSql($text)
    {
        return (is_string($text)? str_replace("\\","\\\\", "'$text'"):$text);
    }

    /**
     * @param array $columns
     * @return $this
     * @throws Exception
     */
    public static function all($columns = ['*'])
    {
        return new static();
    }

    /**
     * @param array|int $id
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
    protected function dynamicWhere($column, $operator = '=', $value = null, $boolean = ' and ')
    {
        self::prepareWhere($column,$operator,$value,$boolean);
        return $this;
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
        $possible_types = [
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "double" => PDO::PARAM_INT,
            "string" => PDO::PARAM_STR,
            "NULL" => PDO::PARAM_NULL,
            "array" => PDO::PARAM_STR,
            "object" => PDO::PARAM_STR,
            "resource" => PDO::PARAM_STR,
            "unknown type" => PDO::PARAM_STR,
        ];

        try
        {
            $builder = self::$connection->prepare($sql);
            foreach ($bindings as $param => $value)
            {
                $builder->bindValue($param,$value,$possible_types[gettype($value)]);
            }
//            dd(static::$query);
            $builder->execute();
            return $builder->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }
    }

    public function orderBy($orders = ['id','ASC'])
    {
        static::$order = ' ORDER BY ';
        if(count($orders)%2!=0)
            $orders[] = 'ASC';

        for($i=0;$i<count($orders);$i+=2)
        {

            static::$order .= "`$orders[$i]`" . ' ' . $orders[$i+1] . ',';
        }
        static::$order = rtrim(static::$order,',');
        return $this;
    }

    public function get($columns = ['*'])
    {
        try
        {
            $column_clause = $columns[0] == '*' ? '*' : implode(' , ', $columns);
            static::$query =
                'SELECT ' .
                $column_clause .
                ' FROM `' .
                static::$table .
                '`' .
                static::$join .
                (empty(static::$where)?'':'` WHERE ' . static::$where) .
                static::$order .
                ';';

            $builder = self::$connection->prepare(self::$query);
//            dd(static::$query);
            $builder->execute();
            return $builder->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }

    }

    public function hasMany(IModel $model,$primary_key = 'id',$foreign_key = '_id')
    {

//        static::$join = ' LEFT JOIN ' . $table .
    }
}