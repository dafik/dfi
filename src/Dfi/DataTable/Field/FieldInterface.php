<?php

namespace Dfi\DataTable\Field;

use Dfi\Iface\Provider;
use ModelCriteria;

interface  FieldInterface
{
    public function getValue($row, &$errors);

    public function getColumns(Provider $query = null);

    public function getAsColumns(Provider $query = null);

    public function setOrder(ModelCriteria $query, $direction);

    public function setOptions($options);

    public function applyFilter(Provider $query, $value, $operator);

}