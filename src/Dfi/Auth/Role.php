<?php

namespace Dfi\Auth;

use Criteria;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Sys\Module;
use Dfi\Iface\Model\Sys\Role as SysRole;
use Dfi\Iface\Provider\Sys\ModuleProvider;
use Dfi\Iface\Provider\Sys\RoleProvider;
use PropelObjectCollection;

/**
 *
 * Enter description here ...
 * @author Szara Eminencja
 *
 */
class Role
{
    public static function getRoles()
    {
        $result = array();
        $providerName = Helper::getClass("iface.provider.sys.role");
        /** @var RoleProvider $provider */
        $provider = $providerName::create();

        $roles = $provider->filterByName('root', Criteria::NOT_EQUAL)->find();
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
        $providerName = Helper::getClass("iface.provider.sys.module");
        /** @var ModuleProvider $provider */
        $provider = $providerName::create();
        $scopeModules = $provider->orderByTreeLeft()->find();
        /* @var $scopeModules PropelObjectCollection */
        $modules = array_merge_recursive($modules, $scopeModules->getArrayCopy());

        foreach ($modules as $module) {
            /* @var $module Module */
            $returnArray[strtolower($module->getId())] = $module->getName();
        }

        return $returnArray;
    }

    public static function getResourcesTab()
    {
        $modules = array();
        $providerName = Helper::getClass("iface.provider.sys.module");
        /** @var ModuleProvider $provider */
        $provider = $providerName::create();
        $scopeModules = $provider->orderByTreeLeft()->find();
        /* @var $scopeModules PropelObjectCollection */

        // $stmt = Propel::getConnection()->query('SELECT max(LENGTH(`name`)) AS max FROM modules');
        // $max = $stmt->fetchColumn(0);


        $modules = array_merge_recursive($modules, $scopeModules->getArrayCopy());

        foreach ($modules as $module) {
            /* @var $module Module */


            $returnArray[strtolower($module->getId())] = array(
                'name' => $module->getName(),
                'module' => $module->getModule(),
                'controller' => $module->getController(),
                'action' => $module->getAction());
        }

        return $returnArray;
    }

    private function getId()
    {
    }

}