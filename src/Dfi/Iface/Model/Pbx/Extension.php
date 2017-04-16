<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Model\Pbx;


use Dfi\Iface\Model;
use PropelObjectCollection;

interface Extension extends Model
{


    public function getName();

    /**
     * @param $orderByRank
     * @return PropelObjectCollection
     */
    public function getPbxPriorities($orderByRank);

    public function getInclude();
}