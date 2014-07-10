<?
class Dfi_Controller_Action_Helper_Menu extends Zend_Controller_Action_Helper_Abstract {
  /**
   * Layout
   *
   * @var Zend_Layout
   */
  private $layout;
  /**
   * View
   *
   * @var Zend_View_Interface
   */
  private $view;
  private $activeBackendTab;
  public function postDispatch(){
    $this->render();
  }
  /**
   * retrive layout from mvc instance
   *
   * @return Zend_Layout
   */
  private function getLayout(){
    return Zend_Layout::getMvcInstance();
  }
  private function render(){
    $this->layout = $this->getLayout();
    $this->view = $this->getActionController()->view;

    $this->view->addBasePath(APPLICATION_PATH.'/views/partials/');

    $this->view->assign('module',$this->getRequest()->getParam('module'));
    $this->view->assign('controller',$this->getRequest()->getParam('controller'));
    $this->view->assign('action',$this->getRequest()->getParam('action'));

    if ( $this->getRequest()->getParam('controller') != 'login'){
      $this->layout->assign('menu',$this->view->render('menus/menuBackend.phtml'));
    }
  }
}