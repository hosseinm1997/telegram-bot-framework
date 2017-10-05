<?php

namespace Robot\Core\Database;


use PDO;
use ReflectionObject;
use ReflectionProperty;
use Robot\Core\BaseClasses\BaseModel;
use Robot\Core\Contracts\IModel;

class DataRow
{
    private $attributes = [];
    private $id = null;
    private $table = null;
    private $primary_key_name = null;

    /**
     * @var IModel $model
     */
    private $model;

    function __construct(IModel $model,$id=null)
    {
        $this->model = $model;
        $this->table = $model->getTableName();

        if(!is_null($id))
        {
            $this->id = $id;
//        $query = sprintf("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE (`TABLE_SCHEMA` = '%s') AND (`TABLE_NAME` = '%s') AND (`COLUMN_KEY` = 'PRI');",env('DATABASE_NAME'),$table);
            $this->primary_key_name = DB::getPrimaryKey($this->table);

            $row = DB::selectFirst($this->table, ['*'], "$this->primary_key_name=$id");
            foreach ($row as $attr => $value)
            {
                if($attr == $this->primary_key_name)
                    continue;

                $this->{$attr} = $value;
//                $this->attributes[$attr] = $value;
            }
        }
    }

    function __get($name)
    {
        if(isset($this->$name))
            return $this->$name;

        return new \Exception('Property not found in' . static::class);
    }

    function __set($name, $value)
    {
        $this->$name = $value;
//        $this->attributes[$name] = $value;
    }

    function save()
    {

        $exceptions = [
            'attributes' => $this->attributes ,
            'table' => $this->table ,
            'primary_key_name' => $this->primary_key_name,
            'model' => $this->model,
        ];

        $private_props = get_object_vars($this);

        $attributes =  array_diff_key($private_props,$exceptions);

        if(is_null($this->id))
            DB::insert($this->table,$attributes);
        else
            DB::update($this->table,$attributes,[$this->primary_key_name => $this->id]);
        return true;
    }
}