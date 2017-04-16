<?php
namespace Dfi\Auth\Adapter;


use Zend_Auth_Adapter_Interface;

interface  AdapterInterface extends Zend_Auth_Adapter_Interface
{

    public function setPassword($password);

    public static function canChangePassword();


}