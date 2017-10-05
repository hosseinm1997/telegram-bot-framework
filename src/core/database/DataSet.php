<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/10/03
 * Time: 8:04 PM
 */

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