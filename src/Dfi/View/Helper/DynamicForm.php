<?php
namespace Dfi\View\Helper;
use Dfi\View\Helper\DynamicForm\Button;
use Dfi\View\Helper\DynamicForm\Callback;
use Dfi\View\Helper\DynamicForm\Map;
use Dfi\View\Helper\DynamicForm\Modal;
use Exception;
use stdClass;
use Zend_View_Helper_Abstract;

/**
 * Helper for rendering javascript code for dynamic form
 *
 */
class DynamicForm extends Zend_View_Helper_Abstract
{
    public function dynamicForm($options, $selector = false, $exportAsFunction = false)
    {
        if (!$options instanceof Modal) {
            throw new Exception('old format');

        } else {
            /* @var  $options Modal */
            $properties = array(
                'selector' => '"' . ($selector ? $selector : $options->getSelector()) . '"',
                'title' => '"' . $options->getTitle() . ' "',
                'openUrl' => '"' . $options->getOpenUrl() . '"',
                'openUrlParams' => json_encode($options->getOpenUrlParams()),
                'buttons' => '{' . $this->formatButtons($options->getButtons()) . '}'
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

        $format = new JSFormat();

        $out = '<script type="text/javascript">' . "\n"
            . $format->JSFormat(''
                . 'jQuery(document).ready(function () {' . "\n"
                . ($exportAsFunction ? '    var  ' . $exportAsFunction . ' = function(){' . "\n" : '')
                . '        processObj.dynamicForm({' . "\n"
                . '            ' . $this->formatOptions($properties) . "\n"
                . '        });' . "\n"
                . '    }' . "\n"
                . ($exportAsFunction ? '    window.' . $exportAsFunction . ' = ' . $exportAsFunction . ';' . "\n" : '')
                . ($exportAsFunction ? '}' : '')
                . ')' . "\n"
            ) . '</script>';
        return $out;
    }

    private function formatButtons($options)
    {
        $buttons = array();

        if ($options && is_array($options)) {
            foreach ($options as $buttonOptions) {

                if (!$buttonOptions instanceof Button) {

                    $button = array(
                        'name' => '"' . $buttonOptions->name . '"',
                        'type' => '"' . $buttonOptions->type . '"',
                        'url' => '"' . $buttonOptions->url . '"',
                    );
                    if (isset($buttonOptions->successCallback)) {
                        $callback = $buttonOptions->successCallback;
                        $button['successCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['successCallback'] = $this->formatCallback(Callback::create());
                    }
                    if (isset($buttonOptions->formCallback)) {
                        $callback = $buttonOptions->formCallback;
                        $button['formCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['formCallback'] = $this->formatCallback(Callback::create());
                    }
                    if (isset($buttonOptions->errorCallback)) {
                        $callback = $buttonOptions->errorCallback;
                        $button['errorCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['errorCallback'] = $this->formatCallback(Callback::create());
                    }
                    if (isset($buttonOptions->reloadCallback)) {
                        $callback = $buttonOptions->reloadCallback;
                        $button['reloadCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['reloadCallback'] = $this->formatCallback(Callback::create());
                    }
                    $buttons[] = "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                } else {
                    $buttonOption = $buttonOptions;
                    /* @var $buttonOption  Button */

                    $type = $buttonOption->getType();

                    if ($type && $type == 'ajax') {

                        $button = array_merge(
                            array(
                                'name' => '"' . $buttonOption->getName() . '"',
                                'type' => '"' . $buttonOption->getType() . '"'

                            ), $buttonOption->getOptions());

                        if ($buttonOption->getUrl()) {
                            $button['url'] = $buttonOption->getUrl();
                        }

                        if ($buttonOption->getSuccessCallback()) {
                            $button['successCallback'] = $this->formatCallback($buttonOption->getSuccessCallback());
                        } else {
                            $button['successCallback'] = $this->formatCallback(Callback::create());
                        }
                        if ($buttonOption->getFormCallback()) {
                            $button['formCallback'] = $this->formatCallback($buttonOption->getFormCallback());
                        } else {
                            $button['formCallback'] = $this->formatCallback(Callback::create());
                        }
                        if ($buttonOption->getErrorCallback()) {
                            $button['errorCallback'] = $this->formatCallback($buttonOption->getErrorCallback());
                        } else {
                            $button['errorCallback'] = $this->formatCallback(Callback::create());
                        }
                        if ($buttonOption->getReloadCallback()) {
                            $button['reloadCallback'] = $this->formatCallback($buttonOption->getReloadCallback());
                        } else {
                            $button['reloadCallback'] = $this->formatCallback(Callback::create());
                        }
                    } else {
                        $button = array_merge(array('name' => '"' . $buttonOption->getName() . '"'), $buttonOption->getOptions());
                        if ($buttonOption->getButtonCallback()) {
                            $button['buttonCallback'] = $this->formatCallback($buttonOption->getButtonCallback());
                        } else {
                            $button['buttonCallback'] = $this->formatCallback(Callback::create());
                        }
                    }
                    $buttons[] = '"' . $buttonOption->getName() . '":' . "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                }
            }
        }

        return implode(",\n", $buttons);

    }

    private function formatCallback($callBack)
    {
        if (!$callBack instanceof Callback) {
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
            /* @var $callBack Callback */
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

    private function formatMap(Map $map)
    {
        if ($map->getItems() > 0) {
            $rows = array();
            foreach ($map->getItems() as $key => $value) {
                $rows[] = $key . ' : "' . $value . '"';
            }

            return '{' . implode(",\n", $rows) . '}';
            //throw new Exception('to do : format map');
        }
        return false;
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





