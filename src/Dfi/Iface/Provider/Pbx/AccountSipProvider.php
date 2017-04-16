<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Provider\Pbx;


use Dfi\Iface\Provider;

interface AccountSipProvider extends Provider
{


    /**
     * @param $sip
     * @return AccountSipProvider
     */
    public function filterByNumber($sip);

}