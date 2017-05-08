<?
namespace Dfi\Form\Element;

use Zend_Form_Element;
use Zend_Form_Element_Multi;
use Zend_Form_Element_Select;

class Select extends Zend_Form_Element_Select
{
    protected $_optionsTranslatorDisabled = false;

    public function getMultiOptions()
    {
        if ($this->optionsTranslatorIsDisabled()) {
            $this->_getMultiOptions();
            return $this->options;
        }
        return parent::getMultiOptions();
    }

    /**
     * Add an option
     *
     * @param  string $option
     * @param  string $value
     * @return Zend_Form_Element_Multi
     */
    public function addMultiOption($option, $value = '')
    {
        $option = (string)$option;
        $this->_getMultiOptions();

        if ($this->optionsTranslatorIsDisabled() || !$this->_translateOption($option, $value)) {
            $this->options[$option] = $value;
        }
        return $this;
    }


    private function optionsTranslatorIsDisabled()
    {
        return $this->_optionsTranslatorDisabled;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setDisableOptionsTranslator($flag)
    {
        $this->_optionsTranslatorDisabled = (bool)$flag;
        return $this;
    }


}