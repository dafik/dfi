<?php

class Dfi_Form_Element_Spinner extends Zend_Form_Element_Text
{
    public $helper = 'formSpinner';

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options = null);
    }
}