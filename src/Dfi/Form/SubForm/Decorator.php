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
        )
    );
}
