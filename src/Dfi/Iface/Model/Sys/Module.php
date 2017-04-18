<?php

namespace Dfi\Iface\Model\Sys;

use Dfi\Iface\Model;

interface Module extends Model
{


    public function getModule();

    public function getController();

    public function getAction();

    public function getId();

    public function getName();

    public function hasChildren();

    public function countChildren();

    public function getFirstChild();
}