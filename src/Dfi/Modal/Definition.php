<?php

namespace Dfi\Modal;

/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 01.05.17
 * Time: 16:54
 */
use BaseObject;
use \Dfi\View\Helper\DynamicForm\Modal;
use PropelObjectCollection;

class Definition
{

    /**
     * @var BaseObject|PropelObjectCollection
     */
    protected $obj;
    /**
     * @var Modal
     */
    protected $modal;
    /**
     * @var string
     */
    protected $label;

    /**
     * @return BaseObject|PropelObjectCollection
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * @param BaseObject|PropelObjectCollection $obj
     */
    public function setObj($obj)
    {
        $this->obj = $obj;
    }

    /**
     * @return Modal
     */
    public function getModal(): Modal
    {
        return $this->modal;
    }

    /**
     * @param Modal $modal
     */
    public function setModal(Modal $modal)
    {
        $this->modal = $modal;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class)
    {
        $this->class = $class;
    }

    /**
     * @var string
     */
    protected $class;


}