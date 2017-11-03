<?php

namespace Dfi\View\Helper;

use Dfi\DataTable as DfiDataTable;
use Dfi\DataTable\ColumnDefinition;
use Dfi\DataTable\Field\FieldAbstract;
use Dfi\View\Helper\DynamicForm\Modal;
use stdClass;
use Zend_Exception;
use Zend_Registry;
use Zend_Translate;
use Zend_Translate_Adapter;
use Zend_View;
use Zend_View_Helper_FormText;

class DataTable extends Zend_View_Helper_FormText
{

    protected static $_translatorDefault;

    protected $_translator;
    protected $_translatorDisabled;

    /**
     * @var DfiDataTable
     */
    private $dt;

    private $dataTableFunctionName = 'DtOptions';
    private $hasColumnDefinition = null;

    private $id;


    /**
     * @var \Dfi\DataTable\Column\Template[]
     */
    private $templates = [];


    public function dataTable(DfiDataTable $dt)
    {
        $this->dt = $dt;

        $table = $this->getTableOpenTag() . "\n" .
            $this->getTableHead() . "\n" .
            $this->getTableBody() . "\n" .
            $this->getTableCloseTag() . "\n" .
            $this->getTableScripts();

        return $table;
    }

    private function getTableOpenTag()
    {
        $dataTags = [];

        $options = $this->dt->getDataOptions();
        $options ['display-length'] = $this->dt->getRequest()->getMaxPerPage();
        $options ['ajax'] = json_encode(array("url" => $this->dt->getAjax(), "type" => "POST"));

        if ($this->hasColumnDefinition()) {
            $options ['data-table-function'] = $this->dataTableFunctionName;
        }

        if ($this->dt->getHasFilter()) {
            $this->dt->setClasses(array_merge($this->dt->getClasses(), array('table-columnfilter')));

            $options ['column-filter'] = $this->renderColumnFilter();
        }

        $attribs = [];

        if ($this->dt->getWidth()) {
            $attribs[] = 'width="' . $this->dt->getWidth() . '"';
        }

        foreach ($options as $optionName => $optionValue) {
            $dataTags[] = 'data-' . $optionName . ' = \'' . $optionValue . '\'';
        }

        $id = $this->dt->getId();
        $this->id = $id;


        $html = '<table id="' . $id . '"' .
            (count($this->dt->getClasses()) > 0 ? ' class="' . implode(' ', $this->dt->getClasses()) . '"' : '') .
            "\n" . implode("\n", $dataTags) .
            "\n" . implode("\n", $attribs) .
            '>';
        return $html;
    }

    private function renderColumnFilter()
    {

        $filter = new stdClass();
        $filter->sPlaceHolder = "head:after";
        $filter->aoColumns = array();

        /** @var FieldAbstract $selectColumn */
        foreach ($this->dt->getSelectColumns() as $selectColumn) {
            if ($selectColumn->hasFilter()) {
                if ($selectColumn->getOption('filter')) {
                    $aoColumn = array('type' => $selectColumn->getOption('filter'));
                    $options = $selectColumn->getOptions();
                    if (array_key_exists('filterData', $options)) {
                        $aoColumn['values'] = $selectColumn->getOption('filterData');
                    }
                    if (array_key_exists('search', $options)) {
                        $aoColumn['search'] = $selectColumn->getOption('search');
                    }
                } else {
                    $aoColumn = array('type' => $selectColumn->getFilter()->getType());
                    $aoColumn['values'] = $selectColumn->getFilter()->getFilterOptions();
                }


                $filter->aoColumns[] = $aoColumn;
            } else {
                $filter->aoColumns[] = null;
            }
        }

        //'{","aoColumns": [ null, {"type": "text"},null, {"type": "select"}, {"type": "select"}, {"type": "text"}, {"type": "text"}, {"type": "text"}]}';

        $json = json_encode($filter);

        return $json;
    }

    private function getTableHead()
    {
        $html = '<thead>' . "\n";
        $columnNames = $this->dt->getSelectColumnsNames();
        if (count($columnNames) > 0) {
            $html .= '<tr>' . "\n";
            foreach ($columnNames as $columnName) {
                if ($this->getTranslator()) {
                    $columnName = $this->getTranslator()->translate($columnName);
                }
                if (false !== strpos($columnName, 'checker')) {
                    $html .= '<th class="checkbox-column"><input type="checkbox" class="uniform"></th>' . "\n";
                } else {
                    $html .= '<th>' . $columnName . '</th>' . "\n";
                }

            }
            $html .= '</tr>' . "\n";
        }
        $html .= '</thead>';

        return $html;

    }

    private function getTableBody()
    {

        return '<tbody></tbody>';
    }

    private function getTableCloseTag()
    {
        return '</table>';
    }

    private function getTableScripts()
    {
        if ($this->hasColumnDefinition() || $this->hasModals() || $this->hasScripts()) {
            $script = '';
            if ($this->hasColumnDefinition()) {
                $columnDefinition = $this->renderColumnDefinitions();

                $script .= 'function ' . $this->dataTableFunctionName . '() {' . "\n";
                $script .= implode("\n", $this->getTemplates()) . "\n";
                $script .= 'return ' . $columnDefinition . "\n";
                $script .= '}' . "\n";
                $script .= 'window[\'' . $this->dataTableFunctionName . '\'] = ' . $this->dataTableFunctionName . "\n";
            }
            $modalsScript = '';

            if ($this->hasModals()) {
                $modalsScript .= '$("#' . $this->id . '").on("draw.dt", function () {' . "\n";
                /** @var Zend_View $view */
                $view = $this->view;
                /** @var DynamicForm $modalHelper */
                $modalHelper = $view->getHelper('dynamicForm');
                /** @var Modal $modal */
                foreach ($this->dt->getModals() as $modal) {
                    $selector = "#" . $this->id . ' ' . $modal->getSelector();
                    $modal->setSelector($selector);
                    $modalsScript .= $modalHelper->getMainScript($modal);
                }
                $modalsScript .= '})' . "\n";

            }
            if ($this->hasScripts()) {
                $scripts = '$("#' . $this->id . '").on("draw.dt", function () {' . "\n";
                foreach ($this->dt->getScripts() as $scriptLine) {
                    $scripts .= $scriptLine;
                }
                $scripts .= '})' . "\n";

            }

            $format = new JSFormat();

            $out = '<script>' . "\n" . $format->JSFormat($script) . '</script>' . "\n";
            $out .= '<script>' . "\n" . $format->JSFormat($modalsScript . $scripts) . '</script>' . "\n";

            return $out;
        }
        return false;

    }

    private function hasColumnDefinition()
    {
        if ($this->hasColumnDefinition !== null) {
            return $this->hasColumnDefinition;
        } else {

            $has = false;
            foreach ($this->dt->getColumnsDefinition() as $columnDefinition) {
                if ($columnDefinition) {
                    $has = true;
                }
            }

            $this->hasColumnDefinition = $has;
            return $has;
        }

    }

    private function getColumnDefinitions()
    {
        $def = new stdClass();
        $def->columns = array();

        $columnIndex = 0;
        /** @var ColumnDefinition|false $columnDefinition */
        foreach ($this->dt->getColumnsDefinition() as $columnDefinition) {
            $column = new stdClass();
            if ($columnDefinition) {
                $column->data = $columnIndex;
                $column->render = $columnDefinition->renderRenderer();
                foreach ($columnDefinition->getOptions() as $optName => $optValue) {
                    $column->$optName = $optValue;
                }
                if ($columnDefinition->hasTemplate()) {
                    $this->templates[] = $columnDefinition->getTemplate();
                }

            } else {
                $column->data = $columnIndex;

            }
            $def->columns[] = $column;
            $columnIndex++;
        };

        return $def;
    }

    private function renderColumnDefinitions()
    {
        $definitions = $this->getColumnDefinitions();

        $json = json_encode($definitions, JSON_PRETTY_PRINT);
        $json = preg_replace('/("render":) "(.*?)"/', '$1$2', $json);
        $json = str_replace('\n', "\n", $json);
        $json = str_replace('\t', "\t", $json);

        return $json;
    }

    private function getTemplates()
    {
        $templates = [];
        foreach ($this->templates as $template) {
            $renderer = 'var ' . $template->getName() . ' = \'' . $template->render() . '\'';
            $templates[] = $renderer;
        }
        return $templates;
    }

    private function hasModals()
    {
        return count($this->dt->getModals()) > 0;

    }

    private function hasScripts()
    {
        return count($this->dt->getScripts()) > 0;
    }


///translator

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
            throw new Zend_Exception('Invalid translator specified');
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
    public
    static function hasDefaultTranslator()
    {
        return (bool)self::$_translatorDefault;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return DataTable
     */
    public
    function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool)$flag;
        return $this;
    }

    /**
     * Is translation disabled?
     *
     * @return bool
     */
    public
    function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }


}
