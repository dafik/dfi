<?php
namespace Dfi\Asterisk;

use PAMI\Client\Impl\ClientImpl;
use Zend_Registry;

class Client extends ClientImpl
{
    public function __construct($options)
    {
        $logger = new \Dfi\Asterisk\Logger(Zend_Registry::get('debugLogger'));
        parent::__construct($options, $logger);
    }
}