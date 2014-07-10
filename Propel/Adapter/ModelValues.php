<?php

class Dfi_Propel_Adapter_ModelValues
{
    public static function  setByArray(BaseObject $model, $values)
    {
        $peer = $model->getPeer();

        $columns = array_flip($peer::getFieldNames(BasePeer::TYPE_FIELDNAME));

        foreach ($values as $name => $value) {
            if (isset($columns[$name])) {
                $phpName = $peer::translateFieldName($name, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME);
                $method = 'set' . $phpName;
                if ($value === '') {
                    $value = null;
                }
                $model->$method($value);
            }
        }
    }
}