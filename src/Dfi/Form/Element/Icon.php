<?php

class Dfi_Form_Element_Icon extends Zend_Form_Element_Text
{
    public $helper = 'formIcon';

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options = null);
    }
}