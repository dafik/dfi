<?php


interface  Dfi_Auth_Adapter_AdapterInterface extends Zend_Auth_Adapter_Interface
{

    public function setPassword($password);

    public static function canChangePassword();


}