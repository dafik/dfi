<?php

namespace Dfi\Error;


use Dfi\App\Config;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Debug\Log;
use Exception;
use Propel;
use Zend_Loader;

class Report
{
    private static $modelName = null;
    const DEFAULT_MODEL_NAME = 'DebugLog';

    public static function save($message, $description = null, $variables = null)
    {
        if (!self::checkModel()) {
            return false;
        }

        try {
            self::checkPropel();
            /** @var Log $log */
            $log = Helper::getObject('iface.debug.log');
            $log->setMessage($message);
            $log->setDescription($description);
            $log->setVariables($variables);

            $log->save();
        } catch (Exception $e) {
            return 'error: ' . $e->getMessage();
        }

        return $log->getGuid();

    }

    public static function saveException(Exception $e, $additional = array())
    {
        if (!self::checkModel()) {
            return false;
        }

        try {
            self::checkPropel();
            /** @var Log $log */
            $log = Helper::getObject('iface.debug.log');

            if ($e instanceof Exception) {
                $log->setMessage($e->getMessage());
                $log->setDescription(isset($e->xdebug_message) ? $e->xdebug_message : $e->getMessage() . ' : ' . $e->getFile() . ' : (' . $e->getLine() . ')' . "\n" . $e->getTraceAsString());
                $log->setFile($e->getFile());
                $log->setLine($e->getLine());
            } else {
                $log->setMessage('unknown');
            }
            try {
                if (!is_array($additional)) {
                    $additional[] = $additional;
                }
                $variables = array(
                    $GLOBALS,
                    $e
                );
                foreach ($additional as $value) {
                    $variables[] = $value;
                }
                $variables = serialize($variables);
            } catch (Exception $e) {
                $variables = serialize(array($GLOBALS));
            }
            $log->setVariables($variables);

            $log->save();
            $log->reload();
        } catch (Exception $e) {
            return 'error: ' . $e->getMessage();
        }

        return $log->getGuid();
    }

    private static function checkPropel()
    {


        if (!Propel::isInit()) {
            require_once APPLICATION_PATH . '/configs/constants.php';
            define('APPLICATION_ENV', 'production');

            if (Zend_Loader::isReadable(Config::get('db.config'))) {

                try {
                    require_once _LIBRARY_PATH . 'propel/Propel.php';
                    Propel::configure(Config::get('db.config'));

                } catch (Exception $e) {
                    throw new Exception('Can\'t setup database: ' . $e->getMessage());
                }
            } else {
                throw new Exception('database config read failed');
            }

        }
    }

    private static function checkModel()
    {
        if (class_exists(self::getModelName())) {
            return true;
        } else {

            if (defined('APPLICATION_PATH')) {

            } else {
                $path = realpath(dirname(__FILE__) . '/../../../');
            }
            if (Propel::isInit()) {
                return false;
            }

            $pathname = $path . '/application/models/' . self::getModelName() . '.php';

            if (file_exists($pathname)) {

                require_once $path . '/library/propel/Propel.php';
                /** @noinspection PhpIncludeInspection */
                require_once $path . '/library/propel/om/Persistent.php';
                require_once $path . '/library/propel/om/BaseObject.php';
                require_once $path . '/application/models/map/' . self::getModelName() . 'TableMap.php';
                require_once $path . '/application/models/om/Base' . self::getModelName() . '.php';
                require_once $path . '/application/models/om/Base' . self::getModelName() . 'Peer.php';
                require_once $path . '/application/models/' . self::getModelName() . '.php';
                require_once $path . '/application/models/' . self::getModelName() . 'Peer.php';


                try {
                    $modelName = self::getModelName();
                    new $modelName();

                    return false;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public static function getModelName()
    {

        if (self::$modelName == null) {
            return self::DEFAULT_MODEL_NAME;
        }
        return self::$modelName;
    }

    /**
     * @param string $modelName
     */
    public static function setModelName($modelName)
    {
        self::$modelName = $modelName;
    }


}