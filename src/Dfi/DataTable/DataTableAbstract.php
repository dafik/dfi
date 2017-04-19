<?php

namespace Dfi\DataTable;

use Dfi\DataTable;
use Zend_Auth;

abstract class DataTableAbstract
{

    protected function getUser()
    {
        return Zend_Auth::getInstance()->getIdentity();
    }

    public function getQuery()
    {
    }

    public function getDataTable()
    {
        return new DataTable();
    }
}