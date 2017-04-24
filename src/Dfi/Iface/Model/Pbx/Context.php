<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Model\Asterisk;


use Dfi\Iface\Model;

interface Context extends Model
{


    public function getName();

    public function getPbxExtensions($orderByRank);
}