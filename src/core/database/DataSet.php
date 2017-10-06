<?php

namespace Robot\Core\Database;


use Robot\Core\Contracts\IModel;

class DataSet
{
    private $rows=[];
    function __construct()
    {

    }

    public function add(DataRow $dataRow)
    {
        $this->rows [] = $dataRow;
    }

    function getAll()
    {
        return $this->rows;
    }
}