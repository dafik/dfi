<?php

class Dfi_Form_Decorator_BootstrapWidget extends Zend_Form_Decorator_HtmlTag
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
        $noAttribs = $this->getOption('noAttribs');


        /** @var Zend_Form $form */
        $form = $this->getElement();

        $name = $this->getElement()->getLegend();

        $isWizard = $this->checkHasClass('wizard');
        if ($isWizard) {
            $wizardGroupCount = count($form->getDisplayGroups());

            $activeTab = $form->getAttrib('activeTab');
            $defaultWizardText = '<span class="step-title">Krok '.$activeTab.' z ' . $wizardGroupCount . '</span>';
            $wizardText = $this->getOption('wizardText');
            $name .= ($wizardText ? $wizardText : $defaultWizardText);
        }


        $this->removeOption('noAttribs');
        $this->removeOption('openOnly');
        $this->removeOption('closeOnly');

        $attribs = null;
        if (!$noAttribs) {
            $attribs = $this->getOptions();
        }

        return $this->template($content, $name);

    }

    private function template($content, $name)
    {

        $template = '' .
            '<div class="widget box wizardBox">' .
            '<div class="widget-header"> <h4><i class="fa fa-reorder"></i> ' . $name . '</h4> <div class="toolbar no-padding"> <div class="btn-group"> <span class="btn btn-xs widget-collapse"><i class="fa fa-angle-down"></i></span> </div> </div> </div>' .
            '<div class="widget-content">' .
            $content .
            '</div>' .
            '</div>';

        return $template;
    }

    private function checkHasClass($search)
    {
        /** @var Zend_Form $form */
        $form = $this->getElement();

        $classes = $form->getAttrib('class');
        if (is_array($classes)) {
            return in_array($search, $classes);
        } else {
            return false !== strpos($classes, $search);
        }
    }
}
