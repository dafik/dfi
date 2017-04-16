<?php
namespace Dfi\DataTable\Field;


use Exception;
use ModelCriteria;

class Join extends FieldAbstract implements FieldInterface
{
    /**
     * @var array|string
     */
    private $glue = ' ';

    /**
     * @var FieldInterface[]
     */
    private $fields = array();

    /**
     * @param $glue
     * @param FieldInterface[] $fields
     */
    public function __construct($glue, array $fields)
    {
        $this->glue = $glue;
        $this->fields = $fields;
    }

    /**
     * @param $glue
     * @param FieldInterface[] $fields
     * @return Join
     */
    public static function create($glue, $fields)
    {
        return new Join($glue, $fields);
    }

    /**
     * @param $row
     * @param $errors
     * @return string
     */
    public function getValue($row, &$errors)
    {
        $values = array();

        foreach ($this->fields as $filed) {
            $values[] = $filed->getValue($row, $errors);
        }

        return implode($this->glue, $values);
    }

    public function getColumns($query = null)
    {
        $columns = [];
        foreach ($this->fields as $filed) {
            $columns = array_merge($columns, $filed->getColumns());
        }
        return $columns;
    }

    public function setOrder(ModelCriteria $query, $direction)
    {
        foreach ($this->fields as $field) {
            $field->setOrder($query, $direction);
        }
    }


    public function applyFilter($query, $value, $operator)
    {
        if ($this->hasOption('filterField') || $this->getFilter()->getFilterField()) {
            parent::applyFilter($query, $value, $operator);
        } else {
            throw new Exception('not implemented yet');
        }
    }


}