<?php

namespace Robot\Core\Contracts;


interface IMessageTypes
{
    function __construct($content,IUserTypes $user=null);
    function sendTo(IUserTypes $user=null);
    function sendToWithKeyboard(IKeyboardTypes $keyboard, IUserTypes $user=null, $meta=[]);
    function setUser(IUserTypes $user);
    function changeContent($content);
    function setNativeAttributes($key, $value);
    function replyTo(IUserTypes $user=null);
    function forceReplyTo($to_user_id,IUserTypes $from_user=null);
    function removeKeyboard();
    function withKeyboard(IKeyboardTypes $keyboard);
}