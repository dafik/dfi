<?php

/**
 * Helper for rendering javascript code for dynamic form
 *
 */
class Dfi_View_Helper_DynamicForm extends Zend_View_Helper_Abstract
{
    public function dynamicForm($options, $selector = false)
    {
        if (!$options instanceof Dfi_View_Helper_DynamicForm_Modal) {
            throw new Exception('old format');

        } else {
            /* @var  $options Dfi_View_Helper_DynamicForm_Modal */
            $properties = array(
                'selector' => '"' . ($selector ? $selector : $options->getSelector()) . '"',
                'title' => '"' . $options->getTitle() . ' "',
                'openUrl' => '"' . $options->getOpenUrl() . '"',
                'buttons' => '[' . $this->formatButtons($options->getButtons()) . ']'
            );
            if ($options->getGetTitle()) {
                $properties['getTitle'] = $this->formatCallback($options->getGetTitle());
            }
            if ($options->getDialogOptions()) {

                $properties['dialogOptions'] = $this->formatMap($options->getDialogOptions());
            }
            if ($options->getOpenSuccessCallback()) {
                $properties['openSuccessCallback'] = $this->formatCallback($options->getOpenSuccessCallback());
            }
            if ($options->getAfterOpenCallback()) {
                $properties['afterOpenCallback'] = $this->formatCallback($options->getAfterOpenCallback());
            }
            if ($options->getBeforeCloseCallback()) {
                $properties['beforeCloseCallback'] = $this->formatCallback($options->getBeforeCloseCallback());
            }
            // beforeClose: function( event, ui ) {}
        }

        $out = '
          <script type="text/javascript">
            jQuery(document).ready(function () {
                setTimeout(function(){
                    processObj.dynamicForm({
                        ' . $this->formatOptions($properties) . '
                    });
                 },500);
            })
          </script>';
        return $out;
    }

    private function formatButtons($options)
    {
        $buttons = array();

        if ($options && is_array($options)) {
            foreach ($options as $buttonOptions) {

                if (!$buttonOptions instanceof Dfi_View_Helper_DynamicForm_Button) {

                    $button = array(
                        'name' => '"' . $buttonOptions->name . '"',
                        'type' => '"' . $buttonOptions->type . '"',
                        'url' => '"' . $buttonOptions->url . '"',
                    );
                    if (isset($buttonOptions->successCallback)) {
                        $callback = $buttonOptions->successCallback;
                        $button['successCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['successCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                    }
                    if (isset($buttonOptions->formCallback)) {
                        $callback = $buttonOptions->formCallback;
                        $button['formCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['formCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                    }
                    if (isset($buttonOptions->errorCallback)) {
                        $callback = $buttonOptions->errorCallback;
                        $button['errorCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['errorCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                    }
                    if (isset($buttonOptions->reloadCallback)) {
                        $callback = $buttonOptions->reloadCallback;
                        $button['reloadCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['reloadCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                    }
                    $buttons[] = "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                } else {
                    $buttonOption = $buttonOptions;
                    /* @var $buttonOption  Dfi_View_Helper_DynamicForm_Button */

                    $type = $buttonOption->getType();

                    if ($type && $type == 'ajax') {
                        $button = array(
                            'name' => '"' . $buttonOption->getName() . '"',
                            'type' => '"' . $buttonOption->getType() . '"',
                            'url' => '"' . $buttonOption->getUrl() . '"',
                        );
                        if ($buttonOption->getSuccessCallback()) {
                            $button['successCallback'] = $this->formatCallback($buttonOption->getSuccessCallback());
                        } else {
                            $button['successCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                        }
                        if ($buttonOption->getFormCallback()) {
                            $button['formCallback'] = $this->formatCallback($buttonOption->getFormCallback());
                        } else {
                            $button['formCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                        }
                        if ($buttonOption->getErrorCallback()) {
                            $button['errorCallback'] = $this->formatCallback($buttonOption->getErrorCallback());
                        } else {
                            $button['errorCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                        }
                        if ($buttonOption->getReloadCallback()) {
                            $button['reloadCallback'] = $this->formatCallback($buttonOption->getReloadCallback());
                        } else {
                            $button['reloadCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                        }
                    } else {
                        $button = array('name' => '"' . $buttonOption->getName() . '"');
                        if ($buttonOption->getButtonCallback()) {
                            $button['buttonCallback'] = $this->formatCallback($buttonOption->getButtonCallback());
                        } else {
                            $button['buttonCallback'] = $this->formatCallback(Dfi_View_Helper_DynamicForm_Callback::create());
                        }
                    }
                    $buttons[] = "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                }
            }
        }
        return implode(",\n", $buttons);

    }

    private function formatCallback($callBack)
    {
        if (!$callBack instanceof Dfi_View_Helper_DynamicForm_Callback) {
            throw new Exception('old format');
            /* @var  $options stdClass */
            /*
            if (isset($callBack->arguments)) {
                $arguments = array();
                foreach ($callBack->arguments as $argument) {
                    $arguments[] = $argument;
                }
                $arguments = implode(',', $arguments);
            }
            if (isset($callBack->steps)) {
                $steps = array();
                foreach ($callBack->steps as $step) {
                    $steps[] = $step;
                }
                $steps = implode(";\n\t\t\t", $steps);
            }*/
        } else {
            /* @var $callBack Dfi_View_Helper_DynamicForm_Callback */
            $arguments = array();
            if ($callBack->getArguments()) {
                foreach ($callBack->getArguments() as $argument) {
                    $arguments[] = $argument;
                }
            }
            $arguments = implode(',', $arguments);
            $steps = array();
            if ($callBack->getSteps()) {
                foreach ($callBack->getSteps() as $step) {
                    $steps[] = $step;
                }
            }
            $steps = implode(";\n\t\t\t", $steps);
        }
        if ($arguments || $steps) {
            $out = 'function(' . $arguments . '){
        ' . "\t\t" . $steps . '
        }';
        } else {
            $out = 'function(){}';
        }
        return $out;

    }

    private function formatMap(Dfi_View_Helper_DynamicForm_Map $map)
    {
        if ($map->getItems() > 0) {
            $rows = array();
            foreach ($map->getItems() as $key => $value) {
                $rows[] = $key . ' : "' . $value . '"';
            }

            return '{' . implode(",\n", $rows) . '}';
            throw new Exception('to do : format map');
        }
    }

    private function formatOptions($options)
    {
        $optionLines = array();
        foreach ($options as $key => $value) {
            $optionLines[] = "\t" . $key . ':' . $value;
        }
        return implode(",\n", $optionLines);
    }

    private function formatButtonOptions($options)
    {
        $optionLines = array();
        foreach ($options as $key => $value) {
            $optionLines[] = "\t\t" . $key . ':' . $value;
        }
        return implode(",\n", $optionLines);
    }
}





