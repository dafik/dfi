<?php

class Dfi_Propel_Map_ColumnMap extends ColumnMap
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
