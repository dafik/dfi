<?php

namespace Dfi\Propel\Map;


use TableMap as PropelTableMap;

class TableMap extends PropelTableMap
{

    protected $description;

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addColumn($name, $phpName, $type, $isNotNull = false, $size = null, $defaultValue = null, $pk = false, $fkTable = null, $fkColumn = null, $description = null)
    {
        $col = new ColumnMap($name, $this);
        $col->setType($type);
        $col->setSize($size);
        $col->setPhpName($phpName);
        $col->setNotNull($isNotNull);
        $col->setDefaultValue($defaultValue);
        $col->setDescription($description);

        if ($pk) {
            $col->setPrimaryKey(true);
            $this->primaryKeys[$name] = $col;
        }

        if ($fkTable && $fkColumn) {
            $col->setForeignKey($fkTable, $fkColumn);
            $this->foreignKeys[$name] = $col;
        }

        $this->columns[$name] = $col;
        $this->columnsByPhpName[$phpName] = $col;
        $this->columnsByInsensitiveCase[strtolower($phpName)] = $col;

        return $col;
    }

    public function addPrimaryKey($columnName, $phpName, $type, $isNotNull = false, $size = null, $defaultValue = null, $description = null)
    {
        return $this->addColumn($columnName, $phpName, $type, $isNotNull, $size, $defaultValue, true, null, null, $description);
    }

    public function addForeignKey($columnName, $phpName, $type, $fkTable, $fkColumn, $isNotNull = false, $size = 0, $defaultValue = null, $description = null)
    {
        return $this->addColumn($columnName, $phpName, $type, $isNotNull, $size, $defaultValue, false, $fkTable, $fkColumn, $description);
    }

    public function addForeignPrimaryKey($columnName, $phpName, $type, $fkTable, $fkColumn, $isNotNull = false, $size = 0, $defaultValue = null, $description = null)
    {
        return $this->addColumn($columnName, $phpName, $type, $isNotNull, $size, $defaultValue, true, $fkTable, $fkColumn, $description);
    }
}
