<?php

namespace Dfi\Iface\Model\Sys;

use Dfi\Auth\Adapter\AdapterInterface;

interface User
{
    /**
     * @return boolean
     */
    public function getActive();

    /**
     * @return AdapterInterface
     */
    public function getAuthAdapter();

    /**
     * @return Role
     */
    public function getSysRole();

    public function getPrimaryKey();


    /**
     * @return string
     */
    public function getLogin();

}