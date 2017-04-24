<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Model\Asterisk;


use Dfi\Iface\Model;

interface Billing extends Model
{

    public function getCcRecfilename();

    public function getUserfield();

    public function getBillsec();

    public function getCalldate($string);

    public function getUniqueid();

    public function setCcRecfilename($filename);

    public function getSrc();

    public function getDstchannel();

    public function getPrimaryKey();
}