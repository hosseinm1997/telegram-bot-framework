<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/09/30
 * Time: 11:10 AM
 */

namespace Robot\Core\Keyboard;


use Robot\Core\BaseClasses\BaseKeyboard;

class ReplyKeyboard extends BaseKeyboard
{
    protected $valid_meta_data = [
        'request_contact',
        'request_location',
    ];
}
