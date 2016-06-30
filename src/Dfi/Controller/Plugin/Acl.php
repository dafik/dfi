<?

class Dfi_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        if ($this->isBypassRequest($request->getModuleName(), $request->getControllerName(), $request->getActionName())) {
            return;
        }

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $acl = Zend_Registry::get('acl');

            $roleId = Zend_Auth::getInstance()->getIdentity()->getSysRole()->getId();

            $privilageName = Dfi_Auth_Acl::getModulesIdsByRequest($request);

            if ($roleId && $privilageName) {

                if ($acl->isAllowed($roleId, $privilageName)) {
                    return;
                }

            }

        }
        $request->setModuleName('default');
        $request->setControllerName('Error');
        $request->setActionName('forbidden');
    }

    private function isBypassRequest($module, $controller, $action)
    {

        $allowedModules = array(
            'soap',
            'ajax'
        );
        if (false !== array_search($module, $allowedModules)) {
            return true;
        }

        $allowedRequests = array(
            'default' => array(
                'login' => array('index' => 0, 'return' => 0),
                'logout' => array('index' => 0),
                'error' => array('error' => 0, 'forbiden' => 0)
            )
        );


        /*$t1 = isset($allowedRequests);
        $t2 = isset($allowedRequests[$module]);
        $t3 = isset($allowedRequests[$module][$controller]);
        $t4 = isset($allowedRequests[$module][$controller][$action]);*/


        if (isset($allowedRequests[$module][$controller][$action])) {
            return true;
        }
        return FALSE;
    }
}