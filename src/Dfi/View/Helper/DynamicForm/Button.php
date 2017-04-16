<?php
namespace Dfi\View\Helper\DynamicForm;

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.02.13
 * Time: 09:42
 * To change this template use File | Settings | File Templates.
 */
class Button
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
     * @var Callback
     */
    protected $successCallback;

    /**
     * @var Callback
     */
    protected $formCallback;

    /**
     * @var Callback
     */
    protected $errorCallback;

    /**
     * @var Callback
     */
    protected $buttonCallback;

    /**
     * @var Callback
     */
    protected $reloadCallback;


    protected $options = [];

    /**
     * @static
     * @return Button
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param $buttonCallback
     * @return Button
     */
    public function setButtonCallback($buttonCallback)
    {
        $this->buttonCallback = $buttonCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getButtonCallback()
    {
        return $this->buttonCallback;
    }


    /**
     * @param $errorCallback
     * @return Button
     */
    public function setErrorCallback($errorCallback)
    {
        $this->errorCallback = $errorCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getErrorCallback()
    {
        return $this->errorCallback;
    }


    /**
     * @param $formCallback
     * @return Button
     */
    public function setFormCallback($formCallback)
    {
        $this->formCallback = $formCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getFormCallback()
    {
        return $this->formCallback;
    }

    /**
     * @param Callback $reloadCallback
     * @return $this
     */
    public function setReloadCallback($reloadCallback)
    {
        $this->reloadCallback = $reloadCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getReloadCallback()
    {
        return $this->reloadCallback;
    }


    /**
     * @param $name
     * @return Button
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
     * @return Button
     */
    public function setSuccessCallback($successCallback)
    {
        $this->successCallback = $successCallback;
        return $this;
    }

    /**
     * @return Callback
     */
    public function getSuccessCallback()
    {
        return $this->successCallback;
    }


    /**
     * @param $type
     * @return Button
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
     * @return Button
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
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param $option
     * @param $value
     * @return $this
     * @internal param array $options
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }


}