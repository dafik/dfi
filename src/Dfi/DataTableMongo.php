<?php

namespace Dfi;

use Criteria;
use Exception;
use Dfi\DataTable\ColumnDefinition;
use Dfi\DataTable\Field\FieldAbstract;
use Dfi\DataTable\Field\FieldInterface;
use Dfi\DataTable\Request;
use Dfi\DataTable\Request\Column;
use Dfi\View\Helper\DynamicForm\Modal;
use ModelCriteria;
use PropelModelPager;
use stdClass;
use Zend_Controller_Action_Helper_Url;
use Zend_Controller_Front;
use Zend_Controller_Request_Http;

class DataTableMongo
{
    /**
     * @var Zend_Controller_Request_Http
     */
    private $httpRequest;

    /**
     * @var  ModelCriteria
     */
    private $query;

    private $page;

    private $maxPerPage;

    private $classes = array('table', 'table-striped', 'table-condensed', 'table-hover', 'table-bordered', 'datatable');
    private $dataOptions = array(
        'processing' => "true",
        'server-side' => "true",
        'paging' => "true",
        'info' => "true",
        "global-search" => "false"
    );
    private $ajax = "";

    private $requestAction = '';

    /**
     * @var FieldInterface[]
     */
    private $selectColumns = [];

    private $columnsDefinition = [];
    private $width = false;

    /**
     * @var Request
     */
    private $request;

    private $id;

    /**
     * @var Modal[]
     */
    private $modals = [];
    private $scripts = [];

    /**
     * @return mixed
     */
    public function getHasFilter()
    {
        return $this->hasFilter;
    }

    private $hasFilter;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Zend_Controller_Request_Http
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }


    /**
     * @param bool|Zend_Controller_Request_Http $request
     */
    public function __construct($request = false)
    {
        if (!$request) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
        }
        $this->httpRequest = $request;
        $this->request = new Request($request);

        $this->page = $this->request->getPage();
        $this->maxPerPage = $this->request->getMaxPerPage();

        $this->id = self::makeId($request->getParams());

    }

    public function __toString()
    {
        return $this->getData();
    }

    public function getData()
    {
        $errors = [];

        try {
            $this->configureSelectColumns();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        try {
            $this->configureOrder();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        try {
            $this->configureFilter();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        $data = new stdClass();
        try {

            /** @var PropelModelPager $pager */
            $pager = $this->query->paginate($this->page, $this->maxPerPage);


            //$data->draw = $this->request->getParam('_');
            //integerJS	The draw counter that this object is a response to - from the draw parameter sent as part of the data request. Note that it is strongly recommended for security reasons that you cast this parameter to an integer, rather than simply echoing back to the client what it sent in the draw parameter, in order to prevent Cross Site Scripting (XSS) attacks.
            $data->recordsTotal = $this->getTotalRows();
            //integerJS	Total records, before filtering (i.e. the total number of records in the database)
            $data->recordsFiltered = $pager->getNbResults();
            //integerJS	Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned for this page of data).
            $data->data = array();
            //arrayJS	The data to be displayed in the table. This is an array of data source objects, one for each row, which will be used by DataTables. Note that this parameter's name can be changed using the ajaxDT option's dataSrc property.
            //$data->error = 'error';
            //stringJS	Optional: If an error occurs during the running of the server-


            $resultsArr = $pager->getResults()->toArray();


            foreach ($resultsArr as $resultRow) {
                $row = [];
                foreach ($this->selectColumns as $fieldHelper) {
                    if (!is_array($resultRow)) {
                        $row[] = $resultRow;
                    } else {
                        $row[] = $fieldHelper->getValue($resultRow, $errors);
                    }
                }
                $data->data[] = $row;
            }

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        if (count($errors) > 0) {
            $data->error = implode("\n", $errors);
        }


        $json = json_encode($data, JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * @param array $params
     * @return array|string
     */
    public static function makeId($params = array())
    {
        //$ids = $this->getHttpRequest()->getParams();
        if (count($params) > 0) {
            $ids = $params;
        } else {
            $ids = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        }
        $id = [];
        foreach ($ids as $key => $value) {
            $id[] = $key . '-' . $value;
        }

        $id = md5(join('-', $id));
        return $id;
    }

    /**
     * @return array|string
     */
    public function getId()
    {
        return $this->id;
    }


    public function getTotalRows()
    {
        $totalQuery = clone $this->query;
        //$totalQuery->

        return $totalQuery->count();
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setMaxPerPage($max)
    {
        $this->maxPerPage = $max;
    }

    /**
     * @param array $selectColumns
     */
    public function setSelectColumns($selectColumns)
    {
        $this->selectColumns = $selectColumns;
    }

    /**
     * @param $label
     * @param FieldAbstract $selectColumn
     * @param bool|ColumnDefinition $dataDefinition
     */
    public function addSelectColumn($label, $selectColumn, $dataDefinition = false)
    {
        $this->selectColumns[$label] = $selectColumn;
        if ($selectColumn->hasFilter()) {
            $this->hasFilter = true;
        }
        $this->columnsDefinition[$label] = $dataDefinition;
    }


    /**
     * @return FieldInterface[]
     */
    public function getSelectColumnsNames()
    {
        return array_keys($this->selectColumns);
    }

    private function configureSelectColumns()
    {
        /** @var ModelCriteria $query */
        $query = $this->query;

        $columns = [];
        $columnsAs = [];

        foreach ($this->selectColumns as $fieldHelper) {
            $columns = array_merge($columns, $fieldHelper->getColumns($query));
        }
        $columns = array_unique($columns);
        $query->select($columns);


        foreach ($this->selectColumns as $fieldHelper) {
            $columnsAs = array_merge($columnsAs, $fieldHelper->getAsColumns($query));
        }

        foreach ($columnsAs as $alias => $sql) {
            $query->addAsColumn($alias, $sql);
        }

        $baseTable = $query->getModelName();
        $joins = $query->getJoins();

        $tmp = [];
        foreach ($joins as $name => $model) {
            if (false !== strpos($name, 'Related')) {
                $tmp[$name] = $model;
                $name = preg_replace('/Related.*/', '', $name);
            }
            $tmp[$name] = $model;
        }
        $joins = $tmp;

        foreach ($columns as $column) {
            if (substr($column, 0, 13) != 'ExtractValue(' && false != strpos($column, '.')) {
                list($table) = explode('.', $column);
                if ($table != $baseTable && 'models\Cc\\' . $table != $baseTable) {
                    if (!array_key_exists($table, $joins)) {
                        $query->joinWith($table, Criteria::LEFT_JOIN);
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getAjax()
    {
        if (!$this->ajax) {

            $params = array_diff($this->httpRequest->getParams(), $this->httpRequest->getPost());
            $params['action'] = $this->requestAction;

            $urlHelper = new Zend_Controller_Action_Helper_Url();
            $url = $urlHelper->url($params);

            $this->setAjax($url);

        }


        return $this->ajax;
    }

    /**
     * @param string $ajax
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    /**
     * @param $class
     */
    public function addClass($class)
    {
        $this->classes[] = $class;
    }

    /**
     * @return array
     */
    public function getDataOptions()
    {
        return $this->dataOptions;
    }

    /**
     * @param array $dataOptions
     */
    public function setDataOptions($dataOptions)
    {
        $this->dataOptions = $dataOptions;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function getColumnsDefinition()
    {
        return $this->columnsDefinition;
    }

    public function setWidth($string)
    {
        $this->width = $string;
    }


    public function getWidth()
    {
        return $this->width;
    }

    private function configureOrder()
    {

        $order = $this->request->getOrder();
        if (count($order) > 0) {
            $this->query->clearOrderByColumns();
            foreach ($order as $orderRequest) {
                $column = $orderRequest->getColumn();
                $selectColumn = $this->getColumnByIndex($column);
                $direction = $orderRequest->getDirection();

                $selectColumn->setOrder($this->query, $direction);
            }
        }

    }

    /**
     * @param $column
     * @return FieldInterface
     */
    private function getColumnByIndex($column)
    {
        $keys = array_keys($this->selectColumns);
        return $this->selectColumns[$keys[$column]];
    }

    /**
     * @return FieldInterface[]
     */
    public function getSelectColumns()
    {
        return $this->selectColumns;
    }

    private function configureFilter()
    {

        if ($this->request->hasSearch()) {

            $columns = $this->request->getColumns();

            /** @var Column $searchRequest */
            foreach ($columns as $columnIndex => $searchRequest) {
                if ($searchRequest->hasSearch()) {
                    $selectColumn = $this->getColumnByIndex($columnIndex);
                    $selectColumn->applyFilter($this->query, $searchRequest->getSearchValue(), $searchRequest->getSearchOperator());


                }
                //$selectColumn->setOrder($this->query, $direction);
            }
        }

    }

    public function addModal(Modal $modal)
    {
        $this->modals[] = $modal;
    }


    /**
     * @return Modal[]
     */
    public function getModals()
    {
        return $this->modals;
    }

    public function addScript($string)
    {
        $this->scripts[] = $string;
    }

    /**
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @param string $requestAction
     */
    public function setRequestAction($requestAction)
    {
        $this->requestAction = $requestAction;
    }

    /**
     * @return string
     */
    public function getRequestAction()
    {
        return $this->requestAction;
    }


    public function getHttpRequestParams()
    {
        $params = $this->httpRequest->getParams();
        unset(
            $params['module'],
            $params['controller'],
            $params['action'],
            $params['draw'],
            $params['columns'],
            $params['start'],
            $params['length'],
            $params['search']
        );
        return $params;
    }

}

