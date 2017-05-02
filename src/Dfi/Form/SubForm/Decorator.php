<?php

namespace Dfi\Form\Subform;

use Dfi\Form\Decorator as FormDecorator;
use Zend_Form;

/**
 * Default Decorators Set
 *
 * This is used to style subforms. These defaults will allow decorators to be applied
 * to subforms that are within current forms.
 *
 * General usage:
 * Dfi\Form\Decorator::setFormDecorator($form, 'div', 'submit', 'cancel');
 * Dfi\Form\Decorator::setFormDecorator(
 *   Instance of form,
 *   Decorator Mode - 3 different options:
 *      - Dfi\Form\Decorator::TABLE     (html table style)
 *      - Dfi\Form\Decorator::DIV       (div style)
 *      - Dfi\Form\Decorator::BOOTSTRAP (twitter bootstrap style)
 *   Name of submit button,
 *   Name of cancel button
 * );
 *
 */
class Decorator extends FormDecorator
{
    /**
     * Form Element Decorator
     *
     * @staticvar array
     */
    protected static $_FormDecorator = array(
        'table' => array(
            'FormElements',
        ),
        'div' => array(
            'FormElements',
        ),
        'bootstrap' => array(
            'FormElements',
            'BootstrapWidget'
        )
    );

    /**
     * Set Form defaults
     * - disable default decorators
     * - set form & displaygroup decorators
     * - set needed prefix path for bootstrap decorators
     * - set form element decorators
     *
     * @param Zend_Form $form The form instance.
     * @param string $format Standard, minimal, table.
     *
     * @return void
     */
    protected static function setFormDefaults(Zend_Form $form, $format)
    {
        $form->setDisableLoadDefaultDecorators(true);
        $form->setDisplayGroupDecorators(self::$_DisplayGroupDecorator[$format]);
        $form->setDecorators(self::$_FormDecorator[$format]);

        if (self::BOOTSTRAP == $format || self::BOOTSTRAP_MINIMAL == $format) {
            $form->addElementPrefixPath(
                'Dfi\\Form\\Decorator',
                'Dfi/Form/Decorator',
                Zend_Form::DECORATOR
            );
            $form->addPrefixPath(
                'Dfi\\Form\\Decorator',
                'Dfi/Form/Decorator',
                Zend_Form::DECORATOR);
        }

        $form->setElementDecorators(self::$_ElementDecorator[$format]);

        return;
    }
}
