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
        //return $content;

        $noAttribs = $this->getOption('noAttribs');


        /** @var Zend_Form $form */
        $form = $this->getElement();
        if ($form) {
            $translator = $form->getTranslator();
            $name = $this->getElement()->getLegend();
            if ($name && $translator) {
                $name = $translator->translate($name);
            }

            $isWizard = $this->checkHasClass('wizard');
            if ($isWizard) {
                $wizardGroupCount = count($form->getDisplayGroups());

                $activeTab = $form->getAttrib('activeTab');
                $defaultWizardText = '<span class="step-title">Krok ' . $activeTab . ' z ' . $wizardGroupCount . '</span>';
                $wizardText = $this->getOption('wizardText');
                $name .= ($wizardText ? $wizardText : $defaultWizardText);

                $this->setOption('class', 'wizardBox');

            }


            $this->removeOption('noAttribs');
            $this->removeOption('openOnly');
            $this->removeOption('closeOnly');


            //legendSub

            $attribs = null;
            if (!$noAttribs) {
                $attribs = $this->getOptions();
                $id = (string)$form->getId();
                if ((!array_key_exists('id', $attribs) || $attribs['id'] == $id) && '' !== $id) {
                    $attribs['id'] = 'fieldset-' . $id;
                }
            }
        } else {
            $name = $this->getOption('name');
            $attribs = $this->getOptions();
        }

        return $this->template($content, $name, $attribs);

    }

    private function template($content, $name, $attribs)
    {


        $class = isset($attribs['class']) ? $attribs['class'] : '';
        if ($class && is_string($class)) {
            if (false != strpos($class, ' ')) {
                $class = explode(' ', $class);
            } else {
                $class = [$class];
            }
        }
        $class[] = 'widget';
        $class[] = 'box';


        $closed = false;
        if (isset($attribs['closed'])) {
            $class[] = 'widget-closed';

        }
        $legendSub = false;
        if (isset($attribs['legendSub'])) {
            $legendSub = $attribs['legendSub'];
        }

        $filter = false;
        if (isset($attribs['filter'])) {
            $filter = $attribs['filter'];
            $filter = $this->formatFilter($filter);
        }

        $attribs['class'] = $class;

        $template = '' .
            '<div ' . $this->_htmlAttribs($attribs) . ' >' .
            '<div class="widget-header"> <h4><i class="fa fa-reorder"></i> ' . $name . '</h4>' .
            ($legendSub ? ' - <span class="subLegend">' . $legendSub . '</span>' : '') .
            ($filter ? ' - <span class="repoFilter" title="' . $filter . '">filter</span>' : '') . ' <div class="toolbar no-padding"> <div class="btn-group"> <span class="btn btn-xs widget-collapse"><i class="fa fa-angle-down"></i></span> </div> </div> </div>' .
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

    private function formatFilter($filter, $step = 0)
    {
        $tmp = '';
        $prefix = str_repeat("\t", $step * 4);
        foreach ($filter as $sub => $val) {
            if (is_array($val)) {
                $ret = $this->formatFilter($val, $step + 1);

                if ($ret) {
                    $tmp .= $prefix . $sub . "\n";
                    $tmp .= $ret;
                } else {
                    $z = 1;
                }

            } else {
                $tmp .= $prefix . $sub . ' : ' . $val . "\n";
            }
        }
        return $tmp;
    }
}
