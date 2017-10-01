<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/09/30
 * Time: 11:12 AM
 */

namespace Robot\Core\Contracts;


interface IKeyboardTypes
{
    function addKey($text,$meta = []);
    function breakToNewRow();
    function addKeyToRow($row_index,$text,$meta = []);
    function addKeyToColumn($column_index,$text,$meta = []);
    function cell($row_index, $column_index, $text='', $meta = []);
    function build();
}