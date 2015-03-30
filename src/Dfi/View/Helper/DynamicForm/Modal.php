<?php

class Dfi_View_Helper_DynamicForm_Modal
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
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $getTitle;

    /**
     * @var Dfi_View_Helper_DynamicForm_Map
     */
    protected $dialogOptions;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $openSuccessCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $afterOpenCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $beforeCloseCallback;


    /**
     * @var  array
     */
    protected $openUrlParams = [];

    /**
     * @static
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param Dfi_View_Helper_DynamicForm_Callback $afterOpenCallback
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function setAfterOpenCallback(Dfi_View_Helper_DynamicForm_Callback $afterOpenCallback)
    {
        $this->afterOpenCallback = $afterOpenCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getAfterOpenCallback()
    {
        return $this->afterOpenCallback;
    }


    /**
     * @param Dfi_View_Helper_DynamicForm_Callback $beforeCloseCallback
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function setBeforeCloseCallback(Dfi_View_Helper_DynamicForm_Callback $beforeCloseCallback)
    {
        $this->beforeCloseCallback = $beforeCloseCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getBeforeCloseCallback()
    {
        return $this->beforeCloseCallback;
    }


    /**
     * @param Dfi_View_Helper_DynamicForm_Button $button
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function addButton(Dfi_View_Helper_DynamicForm_Button $button)
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
     * @param Dfi_View_Helper_DynamicForm_Map $dialogOptions
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function setDialogOptions(Dfi_View_Helper_DynamicForm_Map $dialogOptions)
    {
        $this->dialogOptions = $dialogOptions;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Map
     */
    public function getDialogOptions()
    {
        return $this->dialogOptions;
    }


    /**
     * @param Dfi_View_Helper_DynamicForm_Callback $getTitle
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function setGetTitle(Dfi_View_Helper_DynamicForm_Callback $getTitle)
    {
        $this->getTitle = $getTitle;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getGetTitle()
    {
        return $this->getTitle;
    }


    /**
     * @param Dfi_View_Helper_DynamicForm_Callback $openSuccessCallback
     * @return Dfi_View_Helper_DynamicForm_Modal
     */
    public function setOpenSuccessCallback(Dfi_View_Helper_DynamicForm_Callback $openSuccessCallback)
    {
        $this->openSuccessCallback = $openSuccessCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getOpenSuccessCallback()
    {
        return $this->openSuccessCallback;
    }


    /**
     * @param $openUrl
     * @return Dfi_View_Helper_DynamicForm_Modal
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
     * @return Dfi_View_Helper_DynamicForm_Modal
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
     * @return Dfi_View_Helper_DynamicForm_Modal
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
     * @return Dfi_View_Helper_DynamicForm_Modal
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