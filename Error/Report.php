<?php
require_once '../library/propel/Propel.php';
require_once '../library/propel/om/Persistent.php';
require_once '../library/propel/om/BaseObject.php';
require_once '../application/models/map/DebugLogTableMap.php';
require_once '../application/models/om/BaseDebugLog.php';
require_once '../application/models/om/BaseDebugLogPeer.php';
require_once '../application/models/DebugLog.php';
require_once '../application/models/DebugLogPeer.php';

class Dfi_Error_Report
{
    public static function save($message, $description = null, $variables = null)
    {
        try {
            self::checkPropel();
            $log = new DebugLog();
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
        try {
            self::checkPropel();
            $log = new DebugLog();
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
            define('APPLICATION_ENV','production');

            if (Zend_Loader::isReadable(Dfi_App_Config::get('db.config'))) {

                try {
                    require_once _LIBRARY_PATH . 'propel/Propel.php';
                    Propel::configure(Dfi_App_Config::get('db.config'));

                } catch (Exception $e) {
                    throw new Exception('Can\'t setup database: ' . $e->getMessage());
                }
            } else {
                throw new Exception('database config read failed');
            }

        }
    }

}