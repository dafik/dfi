<?php
namespace Dfi\View\Helper\DynamicForm;

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
     * @var array of Dfi_View_Helper_DynamicForm_Button
     */
    protected $buttons;

    /**
     * @var Callback
     */
    protected $getTitle;

    /**
     * @var Map
     */
    protected $dialogOptions;

    /**
     * @var Callback
     */
    protected $openSuccessCallback;

    /**
     * @var Callback
     */
    protected $afterOpenCallback;

    /**
     * @var Callback
     */
    protected $beforeCloseCallback;


    /**
     * @var  array
     */
    protected $openUrlParams = [];

    /**
     * @static
     * @return Modal
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param Callback $afterOpenCallback
     * @return Modal
     */
    public function setAfterOpenCallback(Callback $afterOpenCallback)
    {
        $this->afterOpenCallback = $afterOpenCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getAfterOpenCallback()
    {
        return $this->afterOpenCallback;
    }


    /**
     * @param Callback $beforeCloseCallback
     * @return Modal
     */
    public function setBeforeCloseCallback(Callback $beforeCloseCallback)
    {
        $this->beforeCloseCallback = $beforeCloseCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getBeforeCloseCallback()
    {
        return $this->beforeCloseCallback;
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
     * @param Callback $getTitle
     * @return Modal
     */
    public function setGetTitle(Callback $getTitle)
    {
        $this->getTitle = $getTitle;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getGetTitle()
    {
        return $this->getTitle;
    }


    /**
     * @param Callback $openSuccessCallback
     * @return Modal
     */
    public function setOpenSuccessCallback(Callback $openSuccessCallback)
    {
        $this->openSuccessCallback = $openSuccessCallback;
        return $this;
    }

    /**
     * @return Callback
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