<?php

namespace Dfi\Iface\Provider\Sys;

use Dfi\Iface\Provider;

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.04.17
 * Time: 13:30
 */
interface RoleProvider extends Provider
{

    /**
     * @param $string
     * @param $NOT_EQUAL
     * @return RoleProvider
     */
    public function filterByName($string, $NOT_EQUAL);
}