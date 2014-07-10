<?

class Dfi_Controller_Plugin_Error extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();

        /** @var $errorHandler Zend_Controller_Plugin_ErrorHandler */
        $front = Zend_Controller_Front::getInstance();
        $errorHandler = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        $errorHandler->setErrorHandlerModule($module);
    }
}