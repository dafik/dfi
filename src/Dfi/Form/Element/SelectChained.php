<?

class Dfi_Form_Element_SelectChained extends Zend_Form_Element_Select
{
    /**
     * Use formSelect view helper by default
     * @var string
     */
    public $helper = 'formSelectChained';


    /**
     * Add an option
     *
     * @param string $chain
     * @param  string $option
     * @param  string $value
     * @return Zend_Form_Element_Multi
     */
    public function addMultiOption($chain, $option, $value = '')
    {
        $option = (string)$option;
        $this->_getMultiOptions();
        $this->options[$chain][$option] = $value;

        return $this;
    }

    /**
     * Add many options at once
     *
     * @param  array $options
     * @return Zend_Form_Element_Multi
     */
    public function addMultiOptions(array $options)
    {
        foreach ($options as $chain => $valueChain) {
            foreach ($valueChain as $option => $value) {
                if (is_array($value)
                    && array_key_exists('key', $value)
                    && array_key_exists('value', $value)
                ) {
                    $this->addMultiOption($chain, $value['key'], $value['value']);
                } else {
                    $this->addMultiOption($chain, $option, $value);
                }
            }
        }
        return $this;
    }

    public function render(Zend_View $view = null)
    {
        if ($view) {
            $view->addHelperPath(Dfi_View_Helper_FormSelectChained::getPath(),'Dfi_View_Helper');
        } else {
            $this->getView()->addHelperPath(Dfi_View_Helper_FormSelectChained::getPath(),'Dfi_View_Helper');
        }

        return parent::render($view);
    }

}