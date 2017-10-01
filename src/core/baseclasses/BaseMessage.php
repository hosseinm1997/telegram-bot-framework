<?php
/**
 * Created by PhpStorm.
 * User: Hossein
 * Date: 2017/09/29
 * Time: 10:22 PM
 */

namespace Robot\Core\BaseClasses;


use Robot\Core\Contracts\IKeyboardTypes;
use Robot\Core\Contracts\IMessageTypes;
use Robot\Core\Contracts\IUserTypes;

abstract class BaseMessage implements IMessageTypes
{

    protected $user=null;
    protected $content=null;
    protected $native_attributes = array();
    protected $params = array();
    protected $keyboard=null;
    private $base_api;
    protected $method_name;

    function __construct($content=null,IUserTypes $user = null)
    {
        $this->base_api = sprintf('https://api.telegram.org/bot%s/',env('token'));
        if(!is_null($content))
            $this->content = $content;
        if(!is_null($user))
            $this->user = $user;
    }

    protected function buildAPI()
    {
        return $this->base_api . $this->method_name;
    }

    protected function postToTelegram($url,$params) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function launch()
    {
        return json_decode($this->postToTelegram($this->buildAPI() , $this->params));
    }

    public function setUser(IUserTypes $user)
    {
        $this->user = $user;
        array_merge($this->params,['chat_id' => $user->chat_id]);
    }

    public function changeContent($content)
    {
        $this->content = $content;
    }

    public function sendTo(IUserTypes $user = null)
    {
        if(!is_null($user))
            $this->user = $user;

        if(!isset($this->user))
        {
            logEvent('No user set');
            return false;
        }

        if(is_null($this->content))
        {
            logEvent('No content set');
            return false;
        }
        $this->addToParams($this->native_attributes);
        $this->addToParams(['chat_id' => $this->user->chat_id]);
        if(!is_null($this->keyboard))
            $this->addToParams(['reply_markup' => $this->keyboard->build()]);

        return true;

    }


    /**
     * @param array $array_data
     */
    protected function addToParams($array_data)
    {
        $this->params = array_merge($this->params,$array_data);
    }

    public function setNativeAttributes($key, $value)
    {
        $this->native_attributes[$key] = $value;
    }

    public function replyTo(IUserTypes $user = null)
    {
        // TODO: Implement replyTo() method.
    }

    public function forceReplyTo($to_user_id, IUserTypes $from_user = null)
    {
        // TODO: Implement forceReplyTo() method.
    }

    public function sendToWithKeyboard(IKeyboardTypes $keyboard, IUserTypes $user = null, $meta = [])
    {
        // TODO: Implement sendToWithKeyboard() method.
    }

    public function removeKeyboard()
    {
        // TODO: Implement removeKeyboard() method.
    }

    public function withKeyboard(IKeyboardTypes $keyboard)
    {
        $this->keyboard = $keyboard;
        return $this;
    }
}