<?php

namespace Dfi\DataTable\Field;

use ModelCriteria;

interface  FieldInterface
{
    public function getValue($row, &$errors);

    public function getColumns(ModelCriteria $query = null);

    public function getAsColumns(ModelCriteria $query = null);

    public function setOrder(ModelCriteria $query, $direction);

    public function setOptions($options);

    public function applyFilter(ModelCriteria $query, $value, $operator);

}