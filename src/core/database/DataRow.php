<?php

namespace Robot\Core\Database;


use Exception;

class DataRow
{
    private $__attributes = [];
    private $__table = null;
    private $__primary_keys = [];
    private $__groups = [];
    private $__exception_property_list = [];

    function __construct($table,$attributes = [],$primary_keys=[])
    {
        $this->__table = $table;

        $this->__exception_property_list = [
            '__attributes'  ,
            '__table'  ,
            '__primary_keys' ,
            '__groups' ,
            '__exception_property_list' ,
        ];

        if(!empty($primary_keys))
        {
//        $query = sprintf("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE (`TABLE_SCHEMA` = '%s') AND (`TABLE_NAME` = '%s') AND (`COLUMN_KEY` = 'PRI');",env('DATABASE_NAME'),$table);
            $this->__primary_keys = $primary_keys;

//            $row = DB::selectFirst($this->__table, ['*'], "$this->__primary_keys=$id");
            foreach ($attributes as $attr => $value)
            {
                if($attr == $this->__primary_keys)
                    continue;

                $this->{$attr} = $value;
//                $this->attributes[$attr] = $value;
            }
        }
    }

    private function generateWhereFromPrimaries()
    {
        $where = '';
        foreach ($this->__primary_keys as $primary_key=>$value) {
            $where .= $primary_key . '=' . $value . ' AND ';
        }
        $where = rtrim($where,' AND ');
        return $where;
    }

    function __get($name)
    {
        if(isset($this->$name))
            return $this->$name;

        $row = DB::selectFirst($this->__table,['*'],$this->generateWhereFromPrimaries());

        if(isset($row[$name]))
        {
            $this->$name = $row[$name];
            return $this->$name;
        }

        return new Exception('Property not found in' . static::class);
    }

    function __set($name, $value)
    {
        if(in_array($name,$this->__groups))
            throw new Exception("Can not change value of object $name");
        $this->$name = $value;
//        $this->attributes[$name] = $value;
    }

    /**
     * @param $name
     * @param $table
     * @return self
     */
    function addRowGroup($name , $table)
    {
        if(in_array($name,$this->__groups))
            return $this->$name;

        $this->$name = new self($table);
        $this->__groups[] = $name;
        return $this->$name;
    }

    function save()
    {
        if(empty($this->__primary_keys))
            return false;

        $private_props = get_object_vars($this);
        $attributes =  array_remove_keys($private_props,array_merge($this->__exception_property_list , $this->__groups));

        if(empty($this->__primary_keys))
            DB::insert($this->__table,$attributes);
        else
            DB::update($this->__table,$attributes ,$this->__primary_keys);
        return true;
    }
}