<?php

namespace Dfi\DataTable\Field;

use Criteria;
use Dfi\Iface\Provider;
use Exception;
use ModelCriteria;
use PropelException;

class Xpath extends FieldAbstract implements FieldInterface
{

    protected $xpath;
    protected $id;

    public function __construct($key, $xpath)
    {
        $this->key = $key;
        $this->xpath = $xpath;
    }

    public static function create($key, $xpath)
    {
        return new Xpath($key, $xpath);
    }

    protected function getExpression($query)
    {
        return 'ExtractValue(' . $this->getRealcolumnName($query) . ', \'' . $this->getXpath() . '\')';
    }

    private function getXpath()
    {
        if (false !== strpos($this->xpath, 'last()')) {
            if (substr($this->xpath, 0, 1) == '(') {
                $this->xpath = substr($this->xpath, 1);
                $pos = strpos($this->xpath, ')');
                $this->xpath = substr($this->xpath, 0, $pos) . substr($this->xpath, $pos + 1);
            }
            return str_replace('//', '/descendant-or-self::', $this->xpath);
        }

        return $this->xpath;
    }

    public function getValue($row, &$errors, $key = false)
    {
        $value = false;
        if (!$key) {
            $key = $this->id;
        }

        if (!isset($row[$key]) && (false !== strpos($key, '.'))) {
            list($subRowName, $key) = explode('.', $key);
            if (isset($row[$subRowName])) {
                $value = $this->getValue($row[$subRowName], $errors, $key);
            } else {
                if ($this->hasOption('notFoundWarn')) {
                    if ($this->getOption('notFoundWarn')) {
                        $errors[] = 'cant found ' . $subRowName . ' in data';
                    }
                } else {
                    $errors[] = 'cant found ' . $subRowName . ' in data';
                }

            }
        } else {
            if (array_key_exists($key, $row)) {
                $value = $row[$key];
            } else {
                if ($this->hasOption('notFoundWarn')) {
                    if ($this->getOption('notFoundWarn')) {
                        $errors[] = 'cant found ' . $key . ' in data';
                    }
                } else {
                    $errors[] = 'cant found ' . $key . ' in data';
                }
            }
        }

        return $value;
    }

    public function getAsColumns($query = null)
    {
        return [$this->getUniqueName() => $this->getExpression($query)];
    }

    public function getUniqueName()
    {
        if (!$this->id) {
            $id = uniqid() . $this->xpath . $this->key;
            $sha = sha1($id);
            $this->id = substr($sha, 0, 10);
        }
        return $this->id;

    }


    public function setOrder(ModelCriteria $query, $order)
    {
        $realColumnName = $this->getExpression($query);

        $order = strtoupper($order);
        switch ($order) {
            case Criteria::ASC:
                $query->addAscendingOrderByColumn($realColumnName);
                break;
            case Criteria::DESC:
                $query->addDescendingOrderByColumn($realColumnName);
                break;
            default:
                throw new PropelException('ModelCriteria::orderBy() only accepts Criteria::ASC or Criteria::DESC as argument');
        }


    }

    public function applyFilter(Provider $query, $value, $operator)
    {
        if ($this->getOption('filter') == 'date-range') {

            list($min, $max) = explode('do', $value);
            $min = trim($min);
            if (strlen($min) == '10') {
                $min .= ' 00:00:00';
            }
            $max = trim($max);
            if (strlen($max) == '10') {
                $max .= ' 23:59:59';
            }
            $value = array(
                'min' => $min,
                'max' => $max
            );
        }


        $key = $this->key;


        if ($this->hasOption('filterField')) {
            $key = $this->getOption('filterField');
            $operator = Criteria::EQUAL;
        }

        if ($this->hasOption('filter') && $this->getOption('filter') == 'number') {
            $operator = Criteria::EQUAL;
        }


        $endUse = 0;

        $method = 'filterByXpath';
        if (false !== strpos($key, '.')) {
            list($model) = explode('.', $key);
            $queryModel = str_replace('models\Cc\\', '', $query->getModelName());
            if ($model != $queryModel) {
                $subQueryMethod = 'use' . $model . 'Query';

                //$z = FormDatumQuery::create()->useFormRelatedByFormDataIdQuery()

                if (method_exists($query, $subQueryMethod)) {
                    $query = $query->$subQueryMethod();
                    $endUse += 1;
                } else {
                    throw new Exception('not implemented yet');
                }
            }
        }
        if (method_exists($query, $method)) {
            $query->$method($this->getXpath(), $value, $operator);
        } else {
            throw new Exception('not implemented yet');
        }

        if ($endUse > 0) {
            $query->endUse();
        }
    }

    /**
     * @param Provider $query
     * @return string
     * @throws Exception
     * @throws PropelException
     */
    protected function getRealcolumnName($query)
    {
        $map = $query->getTableMap();
        $map->buildRelations();

        $columnName = $this->key;
        if (false != strpos($columnName, '.')) {
            list($tableName, $columnName) = explode('.', $columnName);
        } else {
            $tableName = $map->getPhpName();
        }

        if ($tableName != $map->getPhpName()) {
            if ($map->hasRelation($tableName)) {
                $rel = $map->getRelation($tableName);
                $map = $rel->getRightTable();
            } else {
                throw new Exception('unnknown relations');
            }
        }

        if (!$map->hasColumnByPhpName($columnName)) {
            throw new Exception('Unknown column ' . $columnName . ' in model ' . $query->getModelName());
        }


        return $map->getColumnByPhpName($columnName)->getFullyQualifiedName();


    }


}