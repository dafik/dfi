<?php
use PAMI\Client\Impl\ClientImpl;

class Dfi_Asterisk_Client extends ClientImpl
{
    public function __construct($options)
    {
        $logger = new Dfi_Asterisk_Logger(Zend_Registry::get('debugLogger'));
        parent::__construct($options, $logger);
    }
}