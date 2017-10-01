<?php

namespace Robot\Core\BaseClasses;


use Robot\Core\Contracts\IMessageTypes;
use Robot\Core\Contracts\IUserTypes;

class BaseUser implements IUserTypes
{
    protected $chat_id;
    function __construct($chat_id)
    {
        $this->chat_id = $chat_id;
    }

    function __get($name)
    {
        if($name=='chat_id')
        {
            return $this->chat_id;
        }
    }

    function send(IMessageTypes $message)
    {
        $message->sendTo($this);
    }
}