<?php
namespace Dfi\View\Helper;

use Exception;
use Dfi\View\Helper\DynamicForm\Button;
use Dfi\View\Helper\DynamicForm\Callback as Clback;
use Dfi\View\Helper\DynamicForm\Map;
use Dfi\View\Helper\DynamicForm\Modal;
use stdClass;
use Zend_Form;
use Zend_Form_Exception;
use Zend_Registry;
use Zend_Translate;
use Zend_Translate_Adapter;
use Zend_View_Helper_Abstract;

/**
 * Helper for rendering javascript code for dynamic form
 *
 */
class DynamicForm extends Zend_View_Helper_Abstract
{

    protected static $_translatorDefault;

    protected $_translator;
    protected $_translatorDisabled;

    public function dynamicForm($options, $selector = false, $exportAsFunction = false, $eventSelector = false, $event = false)
    {
        $format = new JSFormat();


        $mainScript = $this->getMainScript($options, $selector);


        if ($eventSelector) {

            $mainScript =
                '$(\'' . $eventSelector . '\').on(\'' . $event . '\' ,function(){' . "\n"
                . $mainScript
                . '})';


        } elseif ($exportAsFunction) {
            $mainScript =
                '    var  ' . $exportAsFunction . ' = function(){' . "\n"
                . $mainScript
                . '    }' . "\n"
                . '    window.' . $exportAsFunction . ' = ' . $exportAsFunction . ';' . "\n";
        }


        $out = '<script type="text/javascript">' . "\n" . $format->JSFormat($mainScript . "\n") . '</script>';
        return $out;
    }

    public function getMainScript($options, $selector = false)
    {
        if (!$options instanceof Modal) {
            throw new Exception('old format');

        } else {

            $title = $options->getTitle();
            $title = $title ? $title : '_title.unset';

            $translator = $this->getTranslator();
            if (null !== $translator) {
                $title = $title ? $translator->translate($title) : $title;
            }


            /* @var  $options Modal */
            $properties = array(
                'selector' => '"' . ($selector ? $selector : $options->getSelector()) . '"',
                'title' => '"' . $title . ' "',
                'openUrl' => '"' . $options->getOpenUrl() . '"',
                'openUrlParams' => json_encode($options->getOpenUrlParams()),
                'buttons' => '{' . $this->formatButtons($options->getButtons()) . '}'
            );
            if ($options->getGetTitle()) {
                $properties['getTitle'] = $this->formatCallback($options->getGetTitle());
            }
            if ($options->getTitleCallback()) {
                $properties['titleCallback'] = $this->formatCallback($options->getTitleCallback());
            }
            if ($options->isAutoOpen()) {
                $properties['autoOpen'] = 'true';
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
            if ($options->getBeforeModalCallback()) {
                $properties['beforeModalCallback'] = $this->formatCallback($options->getBeforeModalCallback());
            }
            // beforeClose: function( event, ui ) {}
        }

        $out = 'processObj.dynamicForm({' . "\n"
            . '     ' . $this->formatOptions($properties) . "\n"
            . '});' . "\n";

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
                        $button['successCallback'] = $this->formatCallback(Clback::create());
                    }
                    if (isset($buttonOptions->formCallback)) {
                        $callback = $buttonOptions->formCallback;
                        $button['formCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['formCallback'] = $this->formatCallback(Clback::create());
                    }
                    if (isset($buttonOptions->errorCallback)) {
                        $callback = $buttonOptions->errorCallback;
                        $button['errorCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['errorCallback'] = $this->formatCallback(Clback::create());
                    }
                    if (isset($buttonOptions->reloadCallback)) {
                        $callback = $buttonOptions->reloadCallback;
                        $button['reloadCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['reloadCallback'] = $this->formatCallback(Clback::create());
                    }
                    if (isset($buttonOptions->beforeSendCallback)) {
                        $callback = $buttonOptions->beforeSendCallback;
                        $button['beforeSendCallback'] = $this->formatCallback($callback);
                    } else {
                        $button['beforeSendCallback'] = $this->formatCallback(Clback::create());
                    }
                    $buttons[] = "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                } else {
                    $buttonOption = $buttonOptions;
                    /* @var $buttonOption  Button */

                    $type = $buttonOption->getType();

                    $name = $buttonOption->getName();

                    $translator = $this->getTranslator();
                    if (null !== $translator) {
                        $name = $name ? $translator->translate($name) : $name;
                    }


                    if ($type && $type == 'ajax') {

                        $button = array_merge(
                            array(
                                'name' => '"' . $name . '"',
                                'type' => '"' . $type . '"'

                            ), $buttonOption->getOptions());

                        if ($buttonOption->getUrl()) {
                            $button['url'] = '\'' . $buttonOption->getUrl() . '\'';
                        }

                        if ($buttonOption->getSuccessCallback()) {
                            $button['successCallback'] = $this->formatCallback($buttonOption->getSuccessCallback());
                        } else {
                            $button['successCallback'] = $this->formatCallback(Clback::create());
                        }
                        if ($buttonOption->getFormCallback()) {
                            $button['formCallback'] = $this->formatCallback($buttonOption->getFormCallback());
                        } else {
                            $button['formCallback'] = $this->formatCallback(Clback::create());
                        }
                        if ($buttonOption->getErrorCallback()) {
                            $button['errorCallback'] = $this->formatCallback($buttonOption->getErrorCallback());
                        } else {
                            $button['errorCallback'] = $this->formatCallback(Clback::create());
                        }
                        if ($buttonOption->getReloadCallback()) {
                            $button['reloadCallback'] = $this->formatCallback($buttonOption->getReloadCallback());
                        } else {
                            $button['reloadCallback'] = $this->formatCallback(Clback::create());
                        }
                        if ($buttonOptions->getBeforeSendCallback()) {
                            $button['beforeSendCallback'] = $this->formatCallback($buttonOptions->getBeforeSendCallback());
                        } else {
                            $button['beforeSendCallback'] = $this->formatCallback(Clback::create());
                        }
                    } else {
                        $button = array_merge(array('name' => '"' . $buttonOption->getName() . '"'), $buttonOption->getOptions());
                        if ($buttonOption->getUrl()) {
                            $button['url'] = '\'' . $buttonOption->getUrl() . '\'';
                        }
                        if ($buttonOption->getButtonCallback()) {
                            $button['buttonCallback'] = $this->formatCallback($buttonOption->getButtonCallback());
                        } else {
                            $button['buttonCallback'] = $this->formatCallback(Clback::create());
                        }
                    }
                    $buttons[] = '"' . $name . '":' . "{\n" . $this->formatButtonOptions($button) . "\t\n}";
                }
            }
        }

        return implode(",\n", $buttons);

    }

    private function formatCallback(Clback $callBack)
    {
        if (!$callBack instanceof Clback) {
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
            /* @var $callBack Clback */
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
        $out = '';

        if ($map->getItems() > 0) {
            $rows = array();
            foreach ($map->getItems() as $key => $value) {
                $rows[] = $key . ' : "' . $value . '"';
            }

            $out = '{' . implode(",\n", $rows) . '}';
        }
        return $out;
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

    public static function setDefaultTranslator($translator = null)
    {
        if (null === $translator) {
            self::$_translatorDefault = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            self::$_translatorDefault = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            self::$_translatorDefault = $translator->getAdapter();
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid translator specified');
        }
    }

    /**
     * Retrieve translator object
     *
     * @return Zend_Translate|null
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            return self::getDefaultTranslator();
        }

        return $this->_translator;
    }

    /**
     * Does this form have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool)$this->_translator;
    }

    /**
     * Get global default translator object
     *
     * @return null|Zend_Translate
     */
    public static function getDefaultTranslator()
    {
        if (null === self::$_translatorDefault) {
            // require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('translator')) {
                $translator = Zend_Registry::get('translator');
                if ($translator instanceof Zend_Translate_Adapter) {
                    return $translator;
                } elseif ($translator instanceof Zend_Translate) {
                    return $translator->getAdapter();
                }
            }
        }
        return self::$_translatorDefault;
    }

    /**
     * Is there a default translation object set?
     *
     * @return boolean
     */
    public static function hasDefaultTranslator()
    {
        return (bool)self::$_translatorDefault;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form
     */
    public function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool)$flag;
        return $this;
    }

    /**
     * Is translation disabled?
     *
     * @return bool
     */
    public function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }
}




