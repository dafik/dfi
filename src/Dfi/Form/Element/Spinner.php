<?php
namespace Dfi\Form\Element;

use Zend_Form_Element_Text;

class Spinner extends Zend_Form_Element_Text
{
    public $helper = 'formSpinner';

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options = null);
    }
}