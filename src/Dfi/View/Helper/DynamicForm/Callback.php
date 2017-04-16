<?php
namespace Dfi\View\Helper\DynamicForm;
class Callback
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
     * @return Callback
     */
    public static function create()
    {
        return new self();
    }


    /**
     * @param $argument
     * @return Callback
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
     * @return Callback
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