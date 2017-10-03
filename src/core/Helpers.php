<?php

//include_once '..\interaction\TextMessage.php';
//include_once '..\users\Root.php';
use Robot\Interaction\Text;
use Robot\Users\Root;

function env($key)
{
    $key = strtoupper($key);
    return $GLOBALS[$key]?:null;
}

function getRootDirectory()
{
//    return str_replace('/','\\', $_SERVER['DOCUMENT_ROOT']) . '\teacherinterface_bot\\';
    return str_replace('/','\\', dirname(dirname(__DIR__)) . '\\');
}

function logEvent($message,$file_name=null,$line_number=null)
{
    $file_name = is_null($file_name)? '': " in $file_name" ;
    $line_number = is_null($line_number)? '': " at line $line_number" ;
    try {
        $now = new DateTime('now',new DateTimeZone(env('time_zone')));
        $date = $now->format("Y-m-d H:i:s");
        $log_content = $date . ' -> ' . $message  . $file_name . $line_number . "\n";
        $log_file = fopen(getRootDirectory() . "project.log", 'a');
        fwrite($log_file, $log_content);
        fclose($log_file);
        return true;
    }
    catch(Exception $exception)
    {
        return $exception->getMessage();
    }
}

function dd(...$vars)
{
    $text = '';
    foreach ($vars as $var)
    {
        if(!is_string($var))
            $text .= trim(var_export($var,true),"'") . "\n";
        else
            $text .= $var . "\n";

    }

    $message = new Text($text,new Root(''));
    $message->sendTo();
    exit();
}