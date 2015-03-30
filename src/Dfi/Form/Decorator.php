<?php

/**
 * Default Decorators Set
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
class Dfi_Form_Decorator
{
    /**
     * Constants Definition for Decorator
     */
    const TABLE = 'table';

    const DIV = 'div';

    const BOOTSTRAP = 'bootstrap';

    const BOOTSTRAP_MINIMAL = 'bootstrap_minimal';

    private static $useJsValidation = false;

    /**
     * @param boolean $useJsValidation
     */
    public static function setUseJsValidation($useJsValidation)
    {
        self::$useJsValidation = $useJsValidation;
    }

    /**
     * Element Decorator
     *
     * @staticvar array
     */
    protected static $_ElementDecorator = array(
        'table' => array(
            'ViewHelper',
            array(
                'Description',
                array(
                    'tag' => ''
                )
            ),
            'Errors',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                'Label',
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            array(
                'ViewHelper'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'hint'
                )
            ),
            array(
                'Errors'
            ),
            array(
                'Label'
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div'
                )
            )
        ),
        'bootstrap' => array(
            array(
                'ViewHelper'
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'col-md-10 controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'col-md-2 control-label',
                    'requiredSuffix' => ' <span class="required">*</span>',
                    'escape' => false
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        ),
        'bootstrap_minimal' => array(
            array(
                'ViewHelper'
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'Label'
            )
        )
    );

    /**
     * Captcha Decorator
     *
     * @staticvar array
     */
    protected static $_CaptchaDecorator = array(
        'table' => array(
            'Errors',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                'Label',
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'hint'
                )
            ),
            array(
                'Errors'
            ),
            array(
                'Label'
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div'
                )
            )
        ),
        'bootstrap' => array(
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'control-label'
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        ),
        'bootstrap_minimal' => array(
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'Label'
            )
        )
    );

    /**
     * Captcha Decorator
     *
     * @staticvar array
     */
    protected static $_FileDecorator = array(
        'table' => array(
            'File',
            array(
                'Description',
                array(
                    'tag' => ''
                )
            ),
            'Errors',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                'Label',
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            array(
                'File'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'hint'
                )
            ),
            array(
                'Errors'
            ),
            array(
                'Label'
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div'
                )
            )
        ),
        'bootstrap' => array(
            array(
                'File',
                array(
                    'class' => 'input-file'
                )
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'control-label'
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        ),
        'bootstrap_minimal' => array(
            array(
                'File',
                array(
                    'class' => 'input-file'
                )
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'Label'
            )
        )
    );

    /**
     * Multi Decorator
     *
     * @staticvar array
     */
    protected static $_MultiDecorator = array(
        'table' => array(
            'ViewHelper',
            array(
                'Description',
                array(
                    'tag' => '',
                )
            ),
            'Errors',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                'Label',
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            array(
                'ViewHelper'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'hint'
                )
            ),
            array(
                'Errors'
            ),
            array(
                'Label'
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div'
                )
            )
        ),
        'bootstrap' => array(
            array(
                'ViewHelper'
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                )
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'control-label'
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        ),
        'bootstrap_minimal' => array(
            array(
                'ViewHelper'
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'Description',
                array(
                    'tag' => 'p',
                    'class' => 'help-block',
                )
            ),
            array(
                'Label'
            )
        )
    );

    /**
     * Submit Element Decorator
     *
     * @staticvar array
     */
    protected static $_SubmitDecorator = array(
        'table' => array(
            'ViewHelper',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr',
                    'class' => 'buttons'
                )
            )
        ),
        'div' => array(
            'ViewHelper'
        ),
        'bootstrap' => array(
            'ViewHelper',
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-actions',
                    'openOnly' => false
                )
            )
        ),
        'bootstrap_minimal' => array(
            'ViewHelper'
        )
    );

    /**
     * Reset Element Decorator
     *
     * @staticvar array
     */
    protected static $_ResetDecorator = array(
        'table' => array(
            'ViewHelper',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            'ViewHelper'
        ),
        'bootstrap' => array(
            'ViewHelper',
            array(
                'HtmlTag',
                array(
                    'closeOnly' => false
                )
            )
        ),
        'bootstrap_minimal' => array(
            'ViewHelper'
        )
    );

    /**
     * Hiden Element Decorator
     *
     * @staticvar array
     */
    protected static $_HiddenDecorator = array(
        'table' => array(
            'ViewHelper'
        ),
        'div' => array(
            'ViewHelper'
        ),
        'bootstrap' => array(
            'ViewHelper'
        ),
        'bootstrap_minimal' => array(
            'ViewHelper'
        )
    );

    /**
     * Form Element Decorator
     *
     * @staticvar array
     */
    protected static $_FormDecorator = array(
        'table' => array(
            'FormElements',
            'Form'
        ),
        'div' => array(
            'FormElements',
            'Form'
        ),
        'bootstrap' => array(
            'FormElements',
            'Form',
            'BootstrapWidget'
        ),
        'bootstrap_minimal' => array(
            'FormElements',
            'Form'
        )
    );

    /**
     * DisplayGroup Decorator
     *
     * @staticvar array
     */
    protected static $_DisplayGroupDecorator = array(
        'table' => array(
            'FormElements',
            array(
                'HtmlTag',
                array(
                    'tag' => 'table',
                    'summary' => ''
                )
            ),
            'Fieldset'
        ),
        'div' => array(
            'FormElements',
            'Fieldset'
        ),
        'bootstrap' => array(
            'FormElements',
            'Fieldset'
        ),
        'bootstrap_minimal' => array(
            'FormElements',
            'Fieldset'
        )

    );

    /**
     * ZendX_Jquery Decorator
     *
     * @staticvar array
     */
    protected static $_JqueryElementDecorator = array(
        'table' => array(
            'UiWidgetElement',
            array(
                'Description',
                array(
                    'tag' => '',
                )
            ),
            'Errors',
            array(
                array(
                    'data' => 'HtmlTag'
                ),
                array(
                    'tag' => 'td'
                )
            ),
            array(
                'Label',
                array(
                    'tag' => 'td'
                )
            ),
            array(
                array(
                    'row' => 'HtmlTag'
                ),
                array(
                    'tag' => 'tr'
                )
            )
        ),
        'div' => array(
            array(
                'UiWidgetElement'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'hint'
                )
            ),
            array(
                'Errors'
            ),
            array(
                'Label'
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div'
                )
            )
        ),
        'bootstrap' => array(
            array(
                'UiWidgetElement'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'control-label'
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        ),
        'bootstrap_minimal' => array(
            array(
                'UiWidgetElement'
            ),
            array(
                'Description',
                array(
                    'tag' => 'span',
                    'class' => 'help-block',
                    'style' => 'color: #999;'
                )
            ),
            array(
                'BootstrapErrors'
            ),
            array(
                'BootstrapTag',
                array(
                    'class' => 'controls'
                )
            ),
            array(
                'Label',
                array(
                    'class' => 'control-label'
                )
            ),
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-group'
                )
            )
        )
    );

    public static function overrideDefaults($kind, $type, $newOptions)
    {
        if (!in_array($kind, self::getAllKinds())) {
            throw new Exception('kind: ' . $kind . ' not found!');
        }
        if (!in_array($type, self::getAllTypes())) {
            throw new Exception('kind: ' . $kind . ' not found!');
        }

        $kindGroup = self::$$kind;
        $kindGroup[$type] = $newOptions;
        self::$$kind = $kindGroup;
    }

    public static function getDefaults($kind, $type)
    {
        if (!in_array($kind, self::getAllKinds())) {
            throw new Exception('kind: ' . $kind . ' not found!');
        }
        if (!in_array($type, self::getAllTypes())) {
            throw new Exception('kind: ' . $kind . ' not found!');
        }

        $kindGroup = self::$$kind;
        return $kindGroup[$type];

    }

    public static function getAllKinds()
    {
        return array(
            '_ElementDecorator',
            '_CaptchaDecorator',
            '_FileDecorator',
            '_MultiDecorator',
            '_SubmitDecorator',
            '_ResetDecorator',
            '_HiddenDecorator',
            '_FormDecorator',
            '_DisplayGroupDecorator',
            '_JqueryElementDecorator'
        );
    }

    public static function getAllTypes()
    {
        return array(
            self::TABLE,
            self::DIV,
            self::BOOTSTRAP,
            self::BOOTSTRAP_MINIMAL
        );
    }

    /**
     * Set the form decorators by the given string format or by the default div style
     *
     * @param Zend_Form $form Zend_Form pointer-reference
     * @param string $format Project_Plugin_FormDecoratorDefinition constants
     * @param string $submit_str Element name. (TBD)
     * @param string $cancel_str Element name. (TBD)
     *
     * @return void
     */
    public static function setFormDecorator(
        Zend_Form $form,
        $format = self::BOOTSTRAP,
        $submit_str = 'submit',
        $cancel_str = 'cancel'
    )
    {

        self::setFormDefaults($form, $format);

        self::setButtonDecorators($form, $format, $submit_str, $cancel_str);

        // set hidden, captcha, multi input decorators, file
        /**
         * @var Zend_Form_Element $e
         */
        foreach ($form->getElements() as $e) {
            $classToAdd = [];
            if ($e->getType() == 'Zend_Form_Element_Hidden') {
                $e->setDecorators(self::$_HiddenDecorator[$format]);
            }
            if (is_subclass_of($e, "ZendX_JQuery_Form_Element_UiWidget")) {
                $e->setDecorators(self::$_JqueryElementDecorator[$format]);
            }
            if ($e->getType() == 'Zend_Form_Element_Captcha') {
                $e->setDecorators(self::$_CaptchaDecorator[$format]);
            }
            if ($e->getType() == 'Zend_Form_Element_MultiCheckbox') {
                //$e->setDecorators(self::$_MultiDecorator[$format]);
                //$e->setSeparator('');
                //$e->setAttrib('label_class', 'checkbox');
                //$e->setAttrib("escape", false);
            }
            if ($e->getType() == 'Zend_Form_Element_Radio') {
                //$e->setDecorators(self::$_MultiDecorator[$format]);
                $e->setSeparator('');
                $e->setAttrib('label_class', 'radio');
                $classToAdd[] = 'uniform';

            }
            if ($e->getType() == 'Zend_Form_Element_File') {
                $e->setDecorators(self::$_FileDecorator[$format]);
            }

            if ($e->getType() == 'Zend_Form_Element_Text' || $e->getType() == 'Zend_Form_Element_Textarea' || $e->getType() == 'Dfi_Form_Element_Spinner') {
                $classToAdd[] = 'form-control';
            }

            if ($e->getType() == 'Zend_Form_Element_Select' || $e->getType() == 'Dfi_Form_Element_List') {
                $classToAdd[] = 'select2';
                $classToAdd[] = 'full-width-fix';
            }
            if ($e->getType() == 'Zend_Form_Element_Checkbox' || $e->getType() == 'Zend_Form_Element_MultiCheckbox') {
                $classToAdd[] = 'uniform';
            }


            $tmp = [];
            $classes = $e->getAttrib('class');
            if (is_array($classes)) {
                foreach ($classes as $classRow) {
                    $tmp = array_merge($tmp, explode(' ', $classRow));
                }
            } else {
                $tmp = explode(' ', $classes);
            }

            $classes = array_unique(array_merge($tmp, $classToAdd));
            $e->setAttrib('class', implode(' ', $classes));


            if (self::$useJsValidation) {
                if ($e->isRequired()) {
                    $e->setAttrib('data-rule-required', true);
                }
                $validators = $e->getValidators();
                foreach ($validators as $validator) {

                }
            }

        }
    }

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
            $form->addPrefixPath('Dfi_Form_Decorator',
                'Dfi/Form/Decorator',
                Zend_Form::DECORATOR);
        }

        $form->setElementDecorators(self::$_ElementDecorator[$format]);

        return;
    }

    /**
     * Set Button Decorators
     *
     * @param Zend_Form $form Instance of the form.
     * @param string $format The format (standard, minimal, table).
     * @param string $submit_str Element name of the submit button.
     * @param string $cancel_str Element name of the cancel button.
     *
     * @return void
     */
    protected static function setButtonDecorators(
        Zend_Form $form,
        $format,
        $submit_str,
        $cancel_str
    )
    {
        // set submit button decorators
        if ($form->getElement($submit_str)) {

            $form->getElement($submit_str)->setDecorators(
                self::$_SubmitDecorator[$format]
            );

            if (self::BOOTSTRAP == $format || self::BOOTSTRAP_MINIMAL == $format) {
                $attribs = $form->getElement($submit_str)->getAttrib('class');
                if (empty($attribs)) {
                    $attribs = array('btn', 'btn-primary', 'pull-right');
                } else {
                    if (is_string($attribs)) {
                        $attribs = array($attribs);
                    }
                    $attribs = array_unique(array_merge(array('btn'), $attribs));
                }
                $submitBtn = $form->getElement($submit_str);
                $submitBtn->setAttrib('class', $attribs);

                if (
                    true === ($submitBtn instanceof Zend_Form_Element_Button)
                    && $submitBtn->getAttrib('type') === null
                ) {
                    $submitBtn->setAttrib('type', 'submit');
                }

                if ($form->getElement($cancel_str) && self::BOOTSTRAP == $format) {
                    $form->getElement($submit_str)->getDecorator('HtmlTag')
                        ->setOption('openOnly', true);
                }
            }
            if (self::TABLE == $format) {
                if ($form->getElement($cancel_str)) {
                    $form->getElement($submit_str)->getDecorator('data')
                        ->setOption('openOnly', true);
                    $form->getElement($submit_str)->getDecorator('row')
                        ->setOption('openOnly', true);
                }
            }
        }

        // set cancel button decorators
        if ($form->getElement($cancel_str)) {

            $form->getElement($cancel_str)->setDecorators(
                self::$_ResetDecorator[$format]
            );

            if (self::BOOTSTRAP == $format || self::BOOTSTRAP_MINIMAL == $format) {
                $attribs = $form->getElement($cancel_str)->getAttrib('class');
                if (empty($attribs)) {
                    $attribs = array('btn', 'pull-right');
                } else {
                    if (is_string($attribs)) {
                        $attribs = array($attribs);
                    }
                    $attribs = array_unique(array_merge(array('btn', 'pull-right'), $attribs));
                }
                $form->getElement($cancel_str)
                    ->setAttrib('class', $attribs)
                    ->setAttrib('type', 'reset');
                if ($form->getElement($submit_str) && self::BOOTSTRAP == $format) {
                    $form->getElement($cancel_str)->getDecorator('HtmlTag')
                        ->setOption('closeOnly', true);
                }
            }
            if (self::TABLE == $format) {
                if ($form->getElement($submit_str)) {
                    $form->getElement($cancel_str)->getDecorator('data')
                        ->setOption('closeOnly', true);
                    $form->getElement($cancel_str)->getDecorator('row')
                        ->setOption('closeOnly', true);
                }
            }
        }
    }
}