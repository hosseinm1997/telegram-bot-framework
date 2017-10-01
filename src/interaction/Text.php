<?php

namespace Robot\Interaction;


use Robot\Core\BaseClasses\BaseMessage;
use Robot\Core\Contracts\IUserTypes;

class Text extends BaseMessage
{
    protected $native_attributes = [
        'parse_mode' => null,
        'disable_web_page_preview' => null,
    ];
    protected $method_name = 'sendMessage';


    /**
     * @param string $mode='Markdown' or 'HTML'
     */
    function setParseMode($mode = 'Markdown')
    {
        $this->setNativeAttributes('parse_mode',$mode);
    }

    /**
     * @param boolean $should
     */
    function setDisableWebPagePreview($should)
    {
        $this->setNativeAttributes('parse_mode',$should);
    }

    function sendTo(IUserTypes $user = null)
    {
        if(!parent::sendTo($user))
            return false;
        $this->addToParams(['text' => $this->content]);
        return $this->launch();
    }
}