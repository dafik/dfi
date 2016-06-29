<?php

/**
 *
 * Enter description here ...
 * @author Szara Eminencja
 *
 */
class Dfi_Auth_Role
{
    public static function getRoles()
    {
        $result = array();
        $roles = SysRoleQuery::create()->filterByName('root', Criteria::NOT_EQUAL)->find();
        /** @var $item SysRole */
        foreach ($roles as $item) {
            $result[$item->getId()] = $item->getName();
        }

        return $result;
    }

    public static function getResources()
    {
        $modules = array();
        $returnArray = array();
        $scopeModules = SysModuleQuery::create()->orderByTreeLeft()->find();
        /* @var $scopeModules PropelObjectCollection */
        $modules = array_merge_recursive($modules, $scopeModules->getArrayCopy());

        foreach ($modules as $module) {
            /* @var $module SysModule */
            $returnArray[strtolower($module->getId())] = $module->getName();
        }

        return $returnArray;
    }

    public static function getResourcesTab()
    {
        $modules = array();
        $scopeModules = SysModuleQuery::create()->orderByTreeLeft()->find();
        /* @var $scopeModules PropelObjectCollection */

        // $stmt = Propel::getConnection()->query('SELECT max(LENGTH(`name`)) AS max FROM modules');
        // $max = $stmt->fetchColumn(0);


        $modules = array_merge_recursive($modules, $scopeModules->getArrayCopy());

        foreach ($modules as $module) {
            /* @var $module SysModule */


            $returnArray[strtolower($module->getId())] = array(
                'name' => $module->getName(),
                'module' => $module->getModule(),
                'controller' => $module->getController(),
                'action' => $module->getAction());
        }

        return $returnArray;
    }

}