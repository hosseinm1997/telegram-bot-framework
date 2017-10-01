<?php
/**
 * This user will be considered as developer.
 * Feel free to add development methods.
 */

namespace Robot\Users;


use Robot\Core\BaseClasses\BaseUser;

class Root extends BaseUser
{
    function __construct($chat_id)
    {
        $this->chat_id = env('debug_chat_id');
    }
}