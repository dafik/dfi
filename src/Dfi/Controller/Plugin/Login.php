<?php

namespace Dfi\Controller\Plugin;

use Dfi\App\Config;
use Dfi\Auth\Storage\Cookie;
use Zend_Auth;
use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Request_Http;
use Zend_Layout;

class Login extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        Zend_Auth::getInstance()->setStorage(new Cookie('user'));

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        $bypass = $this->isBypassRequest($request->getModuleName(), $request->getControllerName(), $request->getActionName());

        if (!Zend_Auth::getInstance()->hasIdentity() && !$bypass) {

            if ($request->isXmlHttpRequest()) {

                $response = $this->getResponse();

                $data = array('auth' => true);
                $output = json_encode($data);

                $response->setHeader('Content-Type', 'application/json', true);
                $this->getResponse()->setBody($output);
                $this->getResponse()->sendResponse();
                exit;

            } else {

                if ($request->getControllerName() == 'index' && $request->getActionName() == 'index') {
                    if (!Zend_Layout::getMvcInstance()) {

                        $layoutPath = Config::get('layout.layoutPath');
                        Zend_Layout::startMvc($layoutPath);
                    }
                }
                $request->setControllerName('login');
                $request->setActionName('index');
                $request->setModuleName('default');
            }

        }


    }

    private function isBypassRequest($module, $controller, $action)
    {

        $allowedModules = array();
        if (false !== array_search($module, $allowedModules)) {
            return true;
        }

        $allowedRequests = $this->getAllowedRequests();


        /*$t1 = isset($allowedRequests);
        $t2 = isset($allowedRequests[$module]);
        $t3 = isset($allowedRequests[$module][$controller]);
        $t4 = isset($allowedRequests[$module][$controller][$action]);*/


        if (isset($allowedRequests[$module][$controller][$action])) {
            return true;
        }
        return false;

    }

    /**
     * @return mixed
     */
    protected function getAllowedRequests()
    {

        return array(
            'default' => array(
                'error' => array('error' => 0, 'forbiden' => 0)
            ),
            'admin' => array(
                'login' => array('index' => 0, 'return' => 0),
                'logout' => array('index' => 0),
                'error' => array('error' => 0, 'forbiden' => 0)
            )
        );

    }

}