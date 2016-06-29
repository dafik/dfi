<?php

/**
 * Default Decorators Set
 *
 * This is used to style subforms. These defaults will allow decorators to be applied
 * to subforms that are within current forms.
 *
 * General usage:
 * Dfi_Form_Decorator::setFormDecorator($form, 'div', 'submit', 'cancel');
 * Dfi_Form_Decorator::setFormDecorator(
 *   Instance of form,
 *   Decorator Mode - 3 different options:
 *      - Dfi_Form_Decorator::TABLE     (html table style)
 *      - Dfi_Form_Decorator::DIV       (div style)
 *      - Dfi_Form_Decorator::BOOTSTRAP (twitter bootstrap style)
 *   Name of submit button,
 *   Name of cancel button
 * );
 *
 */
class Dfi_Form_SubForm_Decorator extends Dfi_Form_Decorator
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
                'Dfi_Form_Decorator',
                'Dfi/Form/Decorator',
                Zend_Form::DECORATOR
            );
            $form->addPrefixPath(
                'Dfi_Form_Decorator',
                'Dfi/Form/Decorator',
                Zend_Form::DECORATOR);
        }

        $form->setElementDecorators(self::$_ElementDecorator[$format]);

        return;
    }
}
