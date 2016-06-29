<?php

/**
 * Ez_Form_Decorator_BootstrapErrors
 *
 * Wraps errors in span with class help-inline
 */
class Dfi_Form_Decorator_MultiCheckboxDataTable extends Zend_Form_Decorator_HtmlTag
{
    /**
     * Render content wrapped in an HTML tag
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view = $element->getView();
        if (null === $view) {
            return $content;
        }

        $errors = $element->getMessages();
        if (empty($errors)) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        $formErrorHelper = $view->getHelper('formErrors');
        $formErrorHelper->setElementStart('<span%s>')
            ->setElementSeparator(' | ')
            ->setElementEnd('</span>');

        $errors = $formErrorHelper->formErrors(
            $errors, array('class' => 'has-error help-block')
        );

        switch ($placement) {
            case 'PREPEND':
                return $errors . $separator . $content;
            case 'APPEND':
            default:
                return $content . $separator . $errors;
        }
    }
}
