<?php

namespace Robot\Core\Database;


use Robot\Core\Contracts\IModel;

class DataSet
{
    private $rows=[];
    function __construct(IModel $model,$ids)
    {
        foreach ($ids as $id)
        {

        }
    }
}