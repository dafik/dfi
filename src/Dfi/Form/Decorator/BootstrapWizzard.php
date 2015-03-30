<?php

class Dfi_Form_Decorator_BootstrapWizzard extends Zend_Form_Decorator_HtmlTag
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
        $view = $this->getElement()->getView();
        $element = $this->getElement();

        $groups = $element->getDisplayGroups();

        $groupNames = [];
        /** @var Zend_Form_DisplayGroup $displayGroup */
        $tabIndex = 1;
        foreach ($groups as $displayGroup) {
            $groupNames[$tabIndex] = $displayGroup->getAttrib('legend');
            $tabIndex++;
        }

        $view->assign('groupNames', $groupNames);
        $view->assign('tabs', $content);

        $tabIndex = 1;
        $groupErrors = [];
        if ($element->hasErrors()) {

            /** @var Zend_Form_DisplayGroup $displayGroup */
            foreach ($groups as $displayGroup) {
                /** @var Zend_Form_Element $element */
                foreach ($displayGroup->getElements() as $element) {
                    if ($element->hasErrors()) {
                        if (!isset($groupErrors[$tabIndex])) {
                            $groupErrors[$tabIndex] = [];
                        }
                        $groupErrors[$tabIndex][] = $element->getErrors();
                    }
                }
                $tabIndex++;
            }
        }
        $view->assign('groupErrors', $groupErrors);

        $template = $view->render('botstrapWizzard/wizzard.phtml');

        return $template;
    }
}
