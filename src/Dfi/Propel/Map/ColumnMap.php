<?php

namespace Dfi\Propel\Map;


use ColumnMap as PropelColumnMap;

class ColumnMap extends PropelColumnMap
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

}
