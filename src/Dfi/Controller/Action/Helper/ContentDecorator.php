<?

class Dfi_Controller_Action_Helper_ContentDecorator extends Zend_Controller_Action_Helper_Abstract
{
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

    private $disabled = false;

    private $appendTemplates = array();
    private $prependTemplates = array();


    public function postDispatch()
    {
        if (!$this->disabled) {
            $this->render();
        }
    }

    /**
     * retrive layout from mvc instance
     *
     * @return Zend_Layout
     */
    private function getLayout()
    {
        return Zend_Layout::getMvcInstance();
    }

    private function render()
    {
        $this->layout = $this->getLayout();
        $this->view = $this->getActionController()->view;
        $this->view->addBasePath(APPLICATION_PATH . '/views/partials/');

        $prepend = '';
        $append = '';

        foreach ($this->prependTemplates as $templateName) {
            $prepend .= $this->renderTemplate($templateName);
        }
        foreach ($this->appendTemplates as $templateName) {
            $append .= $this->renderTemplate($templateName);
        }

        if ($append || $prepend) {
            $content = $this->getContent();
            $return = $prepend . $content . $append;
            $this->setContent($return);
        }
    }

    public function prependTemplate($templateName)
    {
        $this->prependTemplates[] = $templateName;
    }

    public function appendTemplate($templateName)
    {
        $this->appendTemplates[] = $templateName;
    }

    public function addTemplate($templateName, $direction)
    {
        if (strtoupper($direction) == 'APPEND') {
            $this->appendTemplate($templateName);
        } elseif (strtoupper($direction) == 'PREPEND') {
            $this->prependTemplate($templateName);
        } else {
            throw new Exception('wrong direction');
        }
    }

    public function removeTemplate($templateName, $direction)
    {
        if (strtoupper($direction) == 'APPEND') {
            $key = array_search($templateName, $this->appendTemplates);
            if ($key != 0) {
                unset($this->appendTemplates[$key]);
            }
        } elseif (strtoupper($direction) == 'PREPEND') {
            $key = array_search($templateName, $this->prependTemplates);
            if ($key != 0) {
                unset($this->prependTemplates[$key]);
            }
        } else {
            throw new Exception('wrong direction');
        }
    }

    private function renderTemplate($templateName)
    {
        if (!strpos($templateName, '.phtml')) {
            $templateName .= '.phtml';
        }
        return $this->view->render($templateName);
    }

    private function getContent()
    {

        $response = $this->getActionController()->getResponse();

        $content = $response->getBody(true);
        $contentKey = $this->layout->getContentKey();

        if (isset($content['default'])) {
            $content = $content['default'];
        }
        //    if ('default' != $contentKey) {
        //      $content = $content[$contentKey];
        //  }
        return $content;
    }

    private function setContent($content)
    {
        $response = $this->getActionController()->getResponse();
        $response->setBody($content);
    }

    public function disable()
    {
        $this->disabled = true;
    }


}