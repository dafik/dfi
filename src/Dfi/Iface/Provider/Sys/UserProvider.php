<?php

namespace Dfi\Iface\Model\Sys;

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