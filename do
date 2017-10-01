#!/usr/bin/php
<?php
include 'responser.php';
use Robot\Core\CommandHandler;
use Robot\Core\Database\DB;

$command_handler = new CommandHandler($argv);

//========================= Terminal Command =========================\\

$command_handler->doCommand('db:migrate',function($parameters = []){
    echo DB::migrateAllSql();
});

$command_handler->doCommand('db:truncate',function($parameters = []){
    //
});

$command_handler->doCommand('test-command',function($parameters = []){
    //
});


//====================================================================\\