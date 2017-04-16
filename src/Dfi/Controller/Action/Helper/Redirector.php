<?
namespace Dfi\Controller\Action\Helper;

use Zend_Controller_Action_Helper_Redirector;

class Dfi_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector {

	public function redirectAndExit()
	{
		$this->getUseAbsoluteUri();
		Messages::getInstance()->save();
		$this->getResponse()->sendHeaders();
		exit();
	}
}
