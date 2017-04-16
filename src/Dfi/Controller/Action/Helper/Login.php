<?
namespace Dfi\Controller\Action\Helper;

use Zend_Auth;
use Zend_Controller_Action_Helper_Abstract;
use Zend_Layout;

class Login extends Zend_Controller_Action_Helper_Abstract {

	public function postDispatch(){
		$this->render();
	}

	private function render(){

		$view = clone $this->getActionController()->view;

		if (Zend_Auth::getInstance()->hasIdentity() && $this->getActionController()->getRequest()->getModuleName() != 'ajax') {
			$view->assign('user',Zend_Auth::getInstance()->getIdentity());
		}
		$view->addBasePath(APPLICATION_PATH.'/views/partials/');
		
		Zend_Layout::getMvcInstance()->assign('login',$view->render('login.phtml'));

	}

}