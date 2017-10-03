<?php

namespace Robot\Core\Database;

use PDO;

class DB
{
    private function __construct(){}
    private function __clone(){}

    private static $connection=null;

    /**
     * @return null|\PDO
     */
    public static function getInstance()
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

    private static function migrateBuiltinSql()
    {
        return self::migrateSql( __DIR__ . "\\builtinsqldumps\\") ? 'built-in tables were migrated successfully' : 'built-in migrating failed';
    }

    public static function migrateUserSql()
    {
        return self::migrateSql(getRootDirectory() . 'src\sqldumps\\') ? 'custom tables were migrated successfully' : 'custom migrating failed' ;
    }

    public static function migrateSql($address)
    {
        $db = static::getInstance();
        $sql_files = array_diff(scandir($address),['.','..']);
        foreach ($sql_files as $sql_file)
        {
            $query = file_get_contents($address.$sql_file);
            $db->exec($query);
        }
        return true;
    }
}