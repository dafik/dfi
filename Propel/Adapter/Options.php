<?php

class Dfi_Propel_Adapter_Options
{
    public static function  get($model)
    {
        $peer = $model . 'Peer';
        $query = $model . 'Query';

        $columns = array();
        /** @var $column ColumnMap */
        foreach ($peer::getTableMap()->getPrimaryKeys() as $column) {
            $columns[] = $column->getPhpName();
        }
        $columns[] = $peer::getFieldNames()[1];
        $options = array();
        foreach ($query::create()->select($columns)->find() as $row) {
            $row = array_values($row);
            $options[$row[0]] = $row[1];
        }

        return $options;
    }
}