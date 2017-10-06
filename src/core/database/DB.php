<?php

namespace Robot\Core\Database;

use PDO;
use PDOStatement;

class DB
{

    /**
     * @var PDO $connection
     * */
    private static $connection=null;

    private function __construct(){}
    private function __clone(){}

    private static function sanitizeValues($input)
    {

        return (is_string($input)? str_replace("\\","\\\\", "'$input'"):$input);

    }

    public static function sanitizeFields($input)
    {
        $result = '';
        if(!is_array($input))
            $input = [$input];
        $first = true;
        foreach ($input as $item)
        {
            if(!$first)
                $result.=',';
            else
                $first=false;

            if(strpos($item,'.'))
            {
                $parts = explode('.',$item);
                $result .= static::sanitizeFields($parts[0]) . '.' . static::sanitizeFields($parts[1]);
                continue;
            }

            if($pos = stripos($item,' AS '))
            {
                $item = str_replace(substr($item,$pos,4),' AS ',$item);
                $parts = explode(' AS ',$item);
                $result .= static::sanitizeFields($parts[0]) . ' AS ' . static::sanitizeFields($parts[1]);
                continue;
            }

            $result .= "`$item`";
        }
        return $result;
    }


    /**
     * @param $statement
     * @param $bindings
     * @return PDOStatement
     */
    private static function bind($statement, $bindings)
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

        foreach ($bindings as $param => $value)
        {
            $statement->bindValue($param,$value,$possible_types[gettype($value)]);
        }
        return $statement;
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

    private static function makeSql($table,$columns=['*'],$where='',$join='',$order='')
    {
        $column_clause = $columns[0] == '*' ? '*' : self::sanitizeFields($columns);
        $query = 'SELECT ' .
            $column_clause .
            ' FROM ' .
            static::sanitizeFields($table) .
            $join .
            (empty($where)?'':' WHERE ' . $where) .
            $order .
            ';';
        return $query;
    }

    private static function migrateBuiltinSql()
    {
        return self::migrateSql( __DIR__ . "\\builtinsqldumps\\") ? 'built-in tables were migrated successfully' : 'built-in migrating failed';
    }

    public static function getPrimaryKeys($table)
    {
        $keys = [];
        static::selectEach('information_schema.COLUMNS',function($row) use (&$keys){
            $keys[] = $row['COLUMN_NAME'];
        },
            ['COLUMN_NAME'],
            sprintf("(`TABLE_SCHEMA` = '%s') AND (`TABLE_NAME` = '%s') AND (`COLUMN_KEY` = 'PRI')", env('DATABASE_NAME'), $table)
        );
        return $keys;
    }
    /**
     * @return null|\PDO
     */
    public static function initialize()
    {
        if(is_null(self::$connection))
        {
            $host = env('host');
            $db_name = env('database_name');
            $port = env('port');
            $charset = env('charset');
            $user_name = env('user_name');
            $password = env('password');
            try
            {
                self::$connection = new \PDO("mysql:host=$host;dbname=$db_name;port=$port;charset=$charset",$user_name,$password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(\PDOException $pdo_exception)
            {
                logEvent($pdo_exception->getMessage());
                dd($pdo_exception->getMessage());
            }
        }
        return self::$connection;
    }

    public static function migrateAllSql()
    {
//        $result = self::migrateBuiltinSql();
        $result = "\n" . self::migrateUserSql();
        return $result;
    }

    public static function migrateUserSql()
    {
        return self::migrateSql(getRootDirectory() . 'src\sqldumps\\') ? 'custom tables were migrated successfully' : 'custom migrating failed' ;
    }

    public static function migrateSql($address)
    {
        $db = static::initialize();
        $sql_files = array_diff(scandir($address),['.','..']);
        foreach ($sql_files as $sql_file)
        {
            $query = file_get_contents($address.$sql_file);
            $db->exec($query);
        }
        return true;
    }

    public static function select($table,$columns=['*'],$where='',$join='',$order='')
    {
        $query = self::makeSql($table,$columns,$where,$join,$order);
        return self::selectRaw($query);
    }

    public static function selectEach($table,\Closure $closure,$columns=['*'],$where='',$join='',$order='')
    {
        $query = self::makeSql($table,$columns,$where,$join,$order);
        $stmt = self::selectRaw($query,'PDO');
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $closure($row);
        }
    }

    public static function selectFirst($table,$columns=['*'],$where='',$join='',$order='')
    {
        $query = self::makeSql($table,$columns,$where,$join,$order);
//        dd($query);
        $stmt = self::selectRaw($query,'PDO');

        if($row = $stmt->fetch(PDO::FETCH_ASSOC))
            return $row;

        return false;
    }

    /**
     * @param $query
     * @param string $return_type
     * @return array|\PDOStatement
     * @throws Exception
     */
    public static function selectRaw($query, $return_type = 'array')
    {
//        dd($query);
        try
        {
            $stmt = static::$connection->prepare($query);
            $stmt->execute();
            if($return_type=='array')
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stmt;
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }
    }

    public static function raw($query)
    {
        try
        {
            $stmt = static::$connection->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }
    }

    public static function insert($table,$attributes)
    {
        try
        {
            $insert_clause = 'INSERT INTO ' . self::sanitizeFields($table) . ' ';
            $column_clause = '(';
            $values_clause ='(';
            $first = true;
            foreach ($attributes as $key => $value)
            {
                if(!$first)
                {
                    $column_clause .= ',';
                    $values_clause .= ',';
                }
                else
                    $first = false;

                $column_clause .= self::sanitizeFields($key);
                $values_clause .= ':' . $key;
            }
            $column_clause .= ')';
            $values_clause .= ')';

            $insert_clause .= $column_clause . ' VALUES ' . $values_clause;

            $statement = static::$connection->prepare($insert_clause);
            $statement = static::bind($statement,$attributes);
//            dd($insert_clause);
            $statement->execute();

            return static::$connection->lastInsertId();
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }
    }

    public static function update($table, $attributes, $conditions)
    {
        try
        {
            $update_clause = 'UPDATE ' . self::sanitizeFields($table) . ' SET ';
            $first = true;
            foreach ($attributes as $key => $value)
            {
                if(!$first)
                    $update_clause .= ',';
                else
                    $first = false;

                $update_clause .= static::sanitizeFields($key) . '=:' . $key;
            }
            if(!empty($conditions) && !is_null($conditions))
            {
                $where_clause = ' WHERE ';
                if(is_array($conditions)) {
                    foreach ($conditions as $key => $value)
                    {
                        $where_clause .= "($key=:__cond__$key) and ";
                        $attributes[":__cond__$key"] = $value;
                    }
                    rtrim($where_clause, ' and ');
                }
                else if(is_string($conditions))
                {
                    $where_clause .= $conditions;
                }
            }
            $update_clause .= $where_clause;

            $statement = static::$connection->prepare($update_clause);
            $statement = static::bind($statement,$attributes);
//            dd($update_clause);
            $statement->execute();

            return DB::select($table,['*'],ltrim($where_clause,' WHERE '));;
        }
        catch(PDOException $pdo_exception)
        {
            throw new Exception($pdo_exception->getMessage() . "\n" . "SQL : " . static::$query);
        }
    }

    public static function delete()
    {

    }
}