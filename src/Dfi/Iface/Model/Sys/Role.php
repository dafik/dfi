<?php

namespace Dfi\Iface\Model\Sys;

use Dfi\Iface\Model;

interface Role extends Model
{


    public function getId();

    public function getEffectiveModules();

    public function getName();
}