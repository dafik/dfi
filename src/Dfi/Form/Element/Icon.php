<?php
namespace Dfi\Form\Element;

use Zend_Form_Element_Text;

class Icon extends Zend_Form_Element_Text
{
    public $helper = 'formIcon';

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options = null);
    }
}