<?php

namespace Dfi\Module;

use Criteria;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Sys\Module;
use Dfi\Iface\Provider\Sys\ModuleProvider;
use DirectoryIterator;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Zend_Loader;

class Resource
{
    public static function getResources(Module $parent, $filter = false, $addEmpty = true, $module = null, $controller = null)
    {
        if ($parent->getAction()) {
            throw new Exception('can\'t add to parent with action');
        }

        $return = array();
        $return[] = 'wybierz';
        if ($controller == null) {
            if ($module == null) {
                $return = array_merge($return, self::getModules($filter));
            } else {
                $return = array_merge($return, self::getControllers($filter, $module));
            }
        } else {
            $return = array_merge($return, self::getActions($module, $controller));
        }
        if ($addEmpty) {
            $return['x'] = 'pusty kontener';
        }
        return $return;
    }

    public static function getAll()
    {

        $modules = self::getModules(false);
        $resM = array();
        foreach ($modules as $module) {
            $controllers = self::getControllers(false, $module);
            $resC = array();
            foreach ($controllers as $cName => $controller) {
                $actions = self::getActions($module, $controller);
                $resA = array();
                foreach ($actions as $action) {
                    $resA[] = $action;
                }
                $resC[$cName] = $resA;
            }
            $resM[$module] = $resC;
        }
        return $resM;
    }


    public static function getModules($filter = false)
    {
        $path = _BASE_PATH . 'application/modules/';
        $notAllowed = array('soap', 'ajax');
        if ($filter) {
            $providerName = Helper::getClass("iface.provider.sys.module");
            /** @var ModuleProvider $provider */
            $provider = $providerName::create();


            $modules = $provider->filterByAction(null)->filterByController(null)->filterByModule(null, Criteria::ISNOTNULL)->find();
            /** @var $module Module */
            foreach ($modules as $module) {
                $notAllowed[] = $module->getModule();
            }
        }
        return self::getFiles($path, $notAllowed);
    }

    public static function getControllers($filter = false, $module)
    {
        $notAllowed = array(
            'AbstractController.php',
            'LoginController.php',
            'LogoutController.php',
            'ErrorController.php',
        );

        $path = _BASE_PATH . '/application/modules/' . $module . '/controllers';

        if ($filter) {
            $providerName = Helper::getClass("iface.provider.sys.module");
            /** @var ModuleProvider $provider */
            $provider = $providerName::create();

            $modules = $provider->filterByAction(null)->filterByController(null)->filterByModule(null, $module)->find();
            /** @var $module Module */
            foreach ($modules as $module) {
                $notAllowed[] = $module->getModule();
            }
        }
        return self::getFiles($path, $notAllowed, -14);

    }

    public static function getActions($filter = false, $module, $controller)
    {

        $controller = ucfirst($controller) . 'Controller.php';
        $loadFile = _BASE_PATH . 'application/modules/' . $module . '/controllers/' . $controller;
        $res = array();

        try {

            if (!class_exists($controller) && Zend_Loader::isReadable($loadFile)) {
                /** @noinspection PhpIncludeInspection */

                $cmd = "php -f " . $loadFile . " -l 2> /dev/null ";
                $message = '/No syntax errors detected/';

                $result = exec($cmd);
                if (preg_match($message, $result)) {
                    include_once $loadFile;
                } else {
                    throw new \Exception($result);
                }

            }

            if ($module == 'default') {
                $controller = substr($controller, 0, -4);
            } else {
                $controller = ucfirst($module) . '_' . substr($controller, 0, -4);
            }

            try {
                $reflection = new ReflectionClass($controller);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);


                foreach ($methods as $method) {
                    $method = $method->name;
                    if (false !== strpos($method, 'Action')) {
                        $name = str_replace('Action', '', $method);
                        $res[$name] = $name;
                    }
                }
            } catch (ReflectionException $e) {
                //TODO $x = 1;
            }
        } catch (Exception $e) {
            $x = 1;
        }

        return $res;
    }

    private static function getFiles($path, $notAllowed = array(), $substring = 0)
    {
        $notDirs = array(
            '.',
            '..',
            '.svn',
        );

        $notAllowed = array_merge($notAllowed, $notDirs);

        $iterator = new DirectoryIterator($path);
        $returnArray = array();

        /** @var $item DirectoryIterator */
        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            $sub = $substring != 0 ? substr($filename, 0, $substring) : $filename;
            $name = strtolower($sub);
            if (!in_array($filename, $notAllowed)) {
                $returnArray[$name] = $name;
            }
        }
        ksort($returnArray);

        return $returnArray;
    }

}