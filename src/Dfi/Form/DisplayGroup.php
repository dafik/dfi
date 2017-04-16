<?php
namespace Dfi\Form;

use Zend_Form_DisplayGroup;

class DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->_form;
    }


}