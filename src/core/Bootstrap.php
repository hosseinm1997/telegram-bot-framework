<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/09/29
 * Time: 3:37 PM
 */

namespace Robot\Core;


use Robot\Core\Database\DB;
use Robot\Core\Keyboard\ReplyKeyboard;
use Robot\Interaction\Text;
use Robot\Users\Root;

class Bootstrap
{
    private function __construct(){}

    private function __clone(){}

    public static function exceptionHandler ($exception=null)
    {
        if(!is_null($exception))
        {
            logEvent($exception->getMessage(),$exception->getFile(),$exception->getLine());
            @dd($exception->getMessage(),$exception->getFile(),$exception->getLine());
        }

    }

    public static function errorHandler ($errno=null, $errstr=null, $errfile = null, $errline = null, $errcontext = null)
    {
        if(!is_null($errstr))
        {
            logEvent($errstr,$errfile,$errline);
            @dd($errstr, $errfile, $errline);
        }
    }

    public static function autoloadRegister($class)
    {
        $last_back_slash = strrpos($class,'\\');
        $file_name = substr($class,$last_back_slash+1);
        $folder = str_replace('robot','src',strtolower(substr($class,0,$last_back_slash+1)));
        $address = getRootDirectory() . $folder . $file_name;
        require_once($address . '.php');
    }

    public static function start()
    {
        date_default_timezone_set(env('time_zone'));

        set_exception_handler(array(static::class, 'exceptionHandler'));
        set_error_handler(array(static::class, 'errorHandler'));
        register_shutdown_function(array(static::class, 'exceptionHandler'));
        spl_autoload_register(array(static::class, 'autoloadRegister'));

//        DB::migrateAllSql();
//        $b = new Root('');
//        $a = new Text("hi\nBye");
//        $keyboard = new ReplyKeyboard();
//        $keyboard->addKey(" sa ");
//        $b->send($a->withKeyboard($keyboard));


//        $a->withKeyboard($keyboard)->sendTo(new Root(''));
    }

}