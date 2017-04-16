<?php

namespace Dfi\Iface\Provider\Sys;

use Criteria;
use Dfi\Iface\Provider;

/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 12.04.17
 * Time: 13:30
 */
interface ModuleProvider extends Provider
{
    /**
     * @param $null
     * @param $comparision
     * @return ModuleProvider
     */

    public function filterByModule($null, $comparision = Criteria::EQUAL);

    /**
     * @param $int
     * @param string $comparision
     * @return ModuleProvider
     * @internal param $GREATER_THAN
     */
    public function filterByTreeLevel($int, $comparision = Criteria::EQUAL);

    /**
     * @param $null
     * @return ModuleProvider
     */
    public function filterByAction($null);

    /**
     * @param $null
     * @return ModuleProvider
     */
    public function filterByController($null);

    /**
     * @return ModuleProvider
     */
    public function orderByTreeLeft();
}