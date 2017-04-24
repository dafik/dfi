<?php

namespace Dfi\Iface\Provider\Sys;

use Dfi\Iface\Model\Sys\User;
use Dfi\Iface\Provider;
use PropelObjectCollection;

interface UserProvider extends Provider
{

    /**
     * @param $username
     * @return PropelObjectCollection
     */
    public function findByByLogin($username);

    /**
     * @param $username
     * @return User
     */
    public function findOneByLogin($username);
}