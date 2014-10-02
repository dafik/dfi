<?php
class Dfi_View_Helper_DynamicForm_Callback
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $steps;


    /**
     * default arguments selector,dialog,data
     *
     * @static
     * @return Dfi_View_Helper_DynamicForm_Callback
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param $argument
     * @return Dfi_View_Helper_DynamicForm_Callback
     */
    public function addArgument($argument)
    {
        $this->arguments[] = $argument;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }


    /**
     * @param $step
     * @return Dfi_View_Helper_DynamicForm_Callback
     */
    public function addStep($step)
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }
}