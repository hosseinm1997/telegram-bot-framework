<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/09/29
 * Time: 10:33 PM
 */

namespace Robot\Core\Contracts;


interface IUserTypes
{
    function __construct($chat_id);

    function send(IMessageTypes $message);

}