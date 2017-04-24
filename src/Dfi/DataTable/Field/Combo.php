<?php

namespace Dfi\DataTable\Field;


use Exception;
use ModelCriteria;

class Combo extends FieldAbstract implements FieldInterface
{
    /**
     * @var FieldInterface[]
     */
    private $fields = array();

    /**
     * @param FieldInterface[] $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param FieldInterface[] $fields
     * @return Combo
     */
    public static function create($fields)
    {
        return new Combo($fields);
    }

    /**
     * @param $row
     * @param $errors
     * @return string
     */
    public function getValue($row, &$errors)
    {
        $values = array();

        foreach ($this->fields as $key => $filed) {
            $values[$key] = $filed->getValue($row, $errors);
        }

        return $values;
    }

    public function getColumns(ModelCriteria $query = null)
    {
        $columns = [];
        foreach ($this->fields as $filed) {
            $columns = array_merge($columns, $filed->getColumns());
        }
        return $columns;
    }

    public function setOrder(ModelCriteria $query, $direction)
    {


        if ($this->getOption("orderField")) {
            $query->orderBy($this->getOption("orderField"), $direction);
        } else {
            // TODO: Implement setOrder() method.
            throw new Exception('not implemented');

        }
    }

}