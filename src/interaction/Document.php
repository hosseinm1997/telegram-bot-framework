<?php

namespace Robot\Interaction;


use Robot\Core\BaseClasses\BaseMessage;
use Robot\Core\Contracts\IUserTypes;

class Document extends BaseMessage
{
    protected $method_name = 'sendDocument';

    function sendTo(IUserTypes $user = null)
    {
        if(!parent::sendTo($user))
            return false;
        $this->addToParams(['file_id' => $this->content]);
        return $this->launch();
    }

}