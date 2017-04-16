<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 14:09
 */

namespace Dfi\Iface;


interface Model
{

    public function save();

    public function reload();

    public function getModifiedColumns();

    public function isNew();

    public function getOldValue($field);

    public function delete();
}