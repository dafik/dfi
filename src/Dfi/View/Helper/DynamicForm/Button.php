<?php

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.02.13
 * Time: 09:42
 * To change this template use File | Settings | File Templates.
 */
class Dfi_View_Helper_DynamicForm_Button
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $successCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $formCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $errorCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $buttonCallback;

    /**
     * @var Dfi_View_Helper_DynamicForm_Callback
     */
    protected $reloadCallback;


    protected $options = [];

    /**
     * @static
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param $buttonCallback
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setButtonCallback($buttonCallback)
    {
        $this->buttonCallback = $buttonCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getButtonCallback()
    {
        return $this->buttonCallback;
    }


    /**
     * @param $errorCallback
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setErrorCallback($errorCallback)
    {
        $this->errorCallback = $errorCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getErrorCallback()
    {
        return $this->errorCallback;
    }


    /**
     * @param $formCallback
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setFormCallback($formCallback)
    {
        $this->formCallback = $formCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getFormCallback()
    {
        return $this->formCallback;
    }

    /**
     * @param \Dfi_View_Helper_DynamicForm_Callback $reloadCallback
     */
    public function setReloadCallback($reloadCallback)
    {
        $this->reloadCallback = $reloadCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getReloadCallback()
    {
        return $this->reloadCallback;
    }


    /**
     * @param $name
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $successCallback
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setSuccessCallback($successCallback)
    {
        $this->successCallback = $successCallback;
        return $this;
    }

    /**
     * @return \Dfi_View_Helper_DynamicForm_Callback
     */
    public function getSuccessCallback()
    {
        return $this->successCallback;
    }


    /**
     * @param $type
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $url
     * @return Dfi_View_Helper_DynamicForm_Button
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param array $options
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }


}