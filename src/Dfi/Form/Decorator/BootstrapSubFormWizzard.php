<?php

class Dfi_Form_Decorator_BootstrapSubFormWizzard extends Zend_Form_Decorator_HtmlTag
{
    private static $activeDisplayed;
    private static $activeTab;

    public function render($content)
    {
        /** @var Zend_Form_DisplayGroup $displayGroup */
        $displayGroup = $this->getElement();
        $isActive = $this->isActiveTab();

        $template =
            '<div class="tab-pane' . ($isActive ? ' active' : '') . '" id="tab' . $displayGroup->getOrder() . '">' .
            '    <h3 class="block padding-bottom-10px">' . $displayGroup->getAttrib('legend') . '</h3>' .
            $content .
            '</div>';

        return $template;
    }

    private function isActiveTab()
    {
        $order = $this->getElement()->getOrder();

        if ($this->findActiveTab() == $order) {
            return true;
        }
        return false;
    }


    private function findActiveTab()
    {
        if (self::$activeTab) {
            return self::$activeTab;
        }

        /** @var Zend_Form_DisplayGroup $displayGroup */
        $displayGroup = $this->getElement();
        $form = $displayGroup->getForm();

        $activeTab = false;

        $key = 1;
        $activeDisplayed = false;
        $hasErrors = $form->hasErrors();

        foreach ($form->getDisplayGroups() as $groupName) {
            $currentHasError = $this->groupHasErrors($groupName);
            if ($key == 1) {
                if ($hasErrors) {
                    if ($currentHasError) {
                        $activeDisplayed = true;
                        $activeTab = $key;
                        break;
                    }
                } else {
                    $activeDisplayed = true;
                    $activeTab = $key;
                    break;
                }
            } else {
                if ($currentHasError && !$activeDisplayed) {
                    $activeDisplayed = true;
                    $activeTab = $key;
                    break;
                }
            }
            $key++;
        }

        self::$activeTab = $activeTab;
        $form->setAttrib('activeTab', $activeTab);

        return self::$activeTab;
    }

    /**
     * @param $group Dfi_Form_DisplayGroup
     */
    private function groupHasErrors($group)
    {
        foreach ($group->getElements() as $element) {
            if ($element->hasErrors()) {
                return true;
            }
        }

        return false;
    }
}
