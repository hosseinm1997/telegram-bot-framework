<?php

namespace Robot\Core\Contracts;


interface IModel
{
    static function raw($sql,$bindings = []);
    static function all($columns = ['*']);
    static function find($ids);
    static function where($column, $operator = '=', $value = null, $boolean = ' and ');
    function orderBy($orders = ['id','ASC']);
    function get($columns = ['*']);
}