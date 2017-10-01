<?php

namespace Robot\Interaction;


use Robot\Core\BaseClasses\BaseMessage;
use Robot\Core\Contracts\IUserTypes;

class Photo extends BaseMessage
{
    protected $method_name = 'sendMessage';

    function sendTo(IUserTypes $user = null)
    {
        if(!parent::sendTo($user))
            return false;
        $this->addToParams(['file_id' => $this->content]);
        return $this->launch();
    }
}