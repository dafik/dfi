<?php

class Dfi_Module_Resource
{
    public static function getResources(SysModule $parent, $filter = false, $addEmpty = true, $module = null, $controller = null)
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


    public static function  getModules($filter = false)
    {
        $path = _BASE_PATH . 'application/modules/';
        $notAllowed = array('soap', 'ajax');
        if ($filter) {
            $modules = SysModuleQuery::create()->filterByAction(null)->filterByController(null)->filterByModule(null, Criteria::ISNOTNULL)->find();
            /** @var $module SysModule */
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
            $modules = SysModuleQuery::create()->filterByAction(null)->filterByController(null)->filterByModule(null, $module)->find();
            /** @var $module SysModule */
            foreach ($modules as $module) {
                $notAllowed[] = $module->getModule();
            }
        }
        return self::getFiles($path, $notAllowed, -14);

    }

    public static function  getActions($filter = false, $module, $controller)
    {

        $controller = ucfirst($controller) . 'Controller.php';
        $loadFile = _BASE_PATH . 'application/modules/' . $module . '/controllers/' . $controller;


        if (Zend_Loader::isReadable($loadFile)) {
            /** @noinspection PhpIncludeInspection */
            include_once $loadFile;
        }

        if ($module == 'default') {
            $controller = substr($controller, 0, -4);
        } else {
            $controller = ucfirst($module) . '_' . substr($controller, 0, -4);
        }
        $res = array();
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

        return $res;
    }

    private function getFiles($path, $notAllowed = array(), $substring = 0)
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