<?php
class Dfi_Asterisk_Logger
{

	/**
	 * Enter description here ...
	 * @var Zend_Log
	 */
	private $log;
	private $debugEnabled;
	

	public function __construct(Zend_Log $log,$debugEnabled = false){
		$this->log = $log;
		$this->debugEnabled = (bool) $debugEnabled;
	}

	public function isDebugEnabled()
	{
		return $this->debugEnabled;
	}
	public function debug($message){
		$message = preg_replace("/[\\n\\r]/", " | ", $message);
		
		$this->log->log($message, Zend_Log::DEBUG);
	}
	
	public function __call($name , array $arguments){
		throw new Exception('Dfi_Asterisk_Logger method:'.$name.' not implemented;');
	}
}

