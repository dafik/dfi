<?
class Dfi_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector {

	public function redirectAndExit()
	{
		$this->getUseAbsoluteUri();
		Dfi_Controller_Action_Helper_Messages::getInstance()->save();
		$this->getResponse()->sendHeaders();
		exit();
	}
}
