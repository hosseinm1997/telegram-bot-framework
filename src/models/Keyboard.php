<?php

namespace Robot\Models;


use Robot\Core\BaseClasses\BaseModel;

class Keyboard extends BaseModel
{
    protected static $table = 'keyboards';

    public static function T()
    {
        return self::$table;
    }
}