<?php
namespace Dfi\View\Helper\DynamicForm;

use Dfi\Error\Exception;
use Dfi\Exception\AppException;
use Dfi\View\Helper\DynamicForm\Callback as Clback;

class Modal
{
    /**
     * @var string
     */
    protected $selector;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $openUrl;

    /**
     * @var Button[]
     */
    protected $buttons;

    /**
     * @var Clback
     */
    protected $getTitle;

    /**
     * @var Map
     */
    protected $dialogOptions;

    /**
     * @var Clback
     */
    protected $titleCallback;

    /**
     * @var Clback
     */
    protected $openSuccessCallback;

    /**
     * @var Clback
     */
    protected $afterOpenCallback;

    /**
     * @var Clback
     */
    protected $beforeCloseCallback;

    /**
     * @var Clback
     */
    protected $beforeModalCallback;

    /**
     * @var  array
     */
    protected $openUrlParams = [];


    protected $autoOpen = false;

    /**
     * @return boolean
     */
    public function isAutoOpen()
    {
        return $this->autoOpen;
    }

    /**
     * @param boolean $autoOpen
     */
    public function setAutoOpen($autoOpen)
    {
        $this->autoOpen = $autoOpen;
    }


    /**
     * @static
     * @return Modal
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param Clback $afterOpenCallback
     * @return Modal
     */
    public function setAfterOpenCallback($afterOpenCallback)
    {
        $this->afterOpenCallback = $afterOpenCallback;
        return $this;
    }

    /**
     * @return Clback
     */
    public function getAfterOpenCallback()
    {
        return $this->afterOpenCallback;
    }


    /**
     * @param callable|Clback $beforeCloseCallback
     * @return Modal
     */
    public function setBeforeCloseCallback($beforeCloseCallback)
    {
        $this->beforeCloseCallback = $beforeCloseCallback;
        return $this;
    }

    /**
     * @return Clback
     */
    public function getBeforeCloseCallback()
    {
        return $this->beforeCloseCallback;
    }

    /**
     * @param Clback $beforeModalCallback
     * @return Modal
     */
    public function setBeforeModalCallback($beforeModalCallback)
    {
        $this->beforeModalCallback = $beforeModalCallback;
        return $this;
    }

    /**
     * @return Clback
     */
    public function getBeforeModalCallback()
    {
        return $this->beforeModalCallback;
    }


    /**
     * @param Button $button
     * @return Modal
     */
    public function addButton(Button $button)
    {
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }


    /**
     * @param Map $dialogOptions
     * @return Modal
     */
    public function setDialogOptions(Map $dialogOptions)
    {
        $this->dialogOptions = $dialogOptions;
        return $this;
    }

    /**
     * @return Map
     */
    public function getDialogOptions()
    {
        return $this->dialogOptions;
    }


    /**
     * @param Clback $getTitle
     * @return Modal
     */
    public function setGetTitle($getTitle)
    {
        throw  new AppException('deprected - use setTitleCallback');
        $this->getTitle = $getTitle;
        return $this;
    }

    /**
     * @return Clback
     */
    public function getGetTitle()
    {
        return $this->getTitle;
    }

    /**
     * @return Clback
     */
    public function getTitleCallback()
    {
        return $this->titleCallback;
    }

    /**
     * @param Clback $titleCallback
     * @return $this
     */
    public function setTitleCallback($titleCallback)
    {
        $this->titleCallback = $titleCallback;
        return $this;
    }


    /**
     * @param Clback $openSuccessCallback
     * @return Modal
     */
    public function setOpenSuccessCallback($openSuccessCallback)
    {
        $this->openSuccessCallback = $openSuccessCallback;
        return $this;
    }

    /**
     * @return Clback
     */
    public function getOpenSuccessCallback()
    {
        return $this->openSuccessCallback;
    }


    /**
     * @param $openUrl
     * @return Modal
     */
    public function setOpenUrl($openUrl)
    {
        $this->openUrl = $openUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpenUrl()
    {
        return $this->openUrl;
    }


    /**
     * @param $selector
     * @return Modal
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }


    /**
     * @param $title
     * @return Modal
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $array
     * @return Modal
     */
    public function setOpenUrlParams($array)
    {
        $this->openUrlParams = $array;
        return $this;
    }

    /**
     * @return array
     */
    public function getOpenUrlParams()
    {
        return $this->openUrlParams;
    }

}