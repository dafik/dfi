<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Model\Pbx;


use Dfi\Iface\Model;

interface Priority extends Model
{


    public function filterByPbxContext($ctx);

    /**
     * @return Extension
     */
    public function getPbxExtension();

    public function getApp();

    public function getData();

    public function getLabel();
}