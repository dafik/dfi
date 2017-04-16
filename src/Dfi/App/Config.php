<?
namespace Dfi\App;

use Exception;
use Zend_Config;
use Zend_Config_Ini;
use Zend_Registry;

class Config
{

    /**
     * @var  Zend_Config
     */
    private static $config;

    /**
     * @param Zend_Config $config
     */
    public static function setConfig($config)
    {
        self::$config = $config;
    }

    private static $configLocation = null;

    private static $mode = null;

    const DEFAULT_MODE = 'production';

    public static function getConfig($asArray = true, $path = false, $default = false, $mode = APPLICATION_ENV, $asString = false)
    {
        if (!self::$config instanceof Zend_Config) {
            if (Zend_Registry::isRegistered('appConfig')) {
                self::$config = Zend_Registry::get('appConfig');
                $config = self::$config;
            } else {

                if (self::hasSection(self::getConfigLocation(), $mode)) {

                    $config = new Zend_Config_Ini(self::getConfigLocation(), $mode, array('allowModifications' => true));

                    if ($mode == APPLICATION_ENV) {
                        self::$config = $config;
                    }
                } else {
                    return $default;
                }
            }
        } else {
            $config = self::$config;
        }


        if ($path) {
            if (strpos($path, '.')) {
                $path = explode('.', $path);
            } else {
                $path = array($path);
            }
            foreach ($path as $key) {
                $config = $config->get($key, $default);
                if (!$config instanceof Zend_Config && array_search($key, $path) != count($path) - 1) {
                    if ($default === false) {
                        throw new Exception('config path ' . implode('.', $path) . ' not found');
                    }
                    $config = $default;
                    break;
                }
            }
        }
        if ($asString) {
            if ($config instanceof Zend_Config) {
                return implode("|", $config->toArray());
            } else {
                return $config;
            }
        }
        if ($asArray) {
            if ($config instanceof Zend_Config) {
                return $config->toArray();
            } else {
                return $config;
            }
        } else {
            return $config;
        }
    }


    public static function get($path = false, $default = false, $asArray = false, $mode = APPLICATION_ENV)
    {
        return self::getConfig($asArray, $path, $default, $mode);
    }


    /**
     * @param bool $path
     * @param bool $default
     * @param string $mode
     * @return string
     */
    public static function getString($path = false, $default = false, $mode = APPLICATION_ENV)
    {
        return self::getConfig(false, $path, $default, $mode, true);
    }

    public static function hasSection($file, $section)
    {
        $ini = parse_ini_file($file, true);
        $keys = array_keys($ini);
        foreach ($keys as $index => $value) {
            if (false !== strpos($value, ':')) {
                list($value) = explode(':', $value);
                unset($keys[$index]);
                $keys[] = trim($value);
            }
        }
        $keys = array_unique($keys);
        sort($keys);

        return false !== array_search($section, $keys);
    }

    /**
     * @return string
     */
    private static function getConfigLocation()
    {
        if (self::$configLocation) {
            return self::$configLocation;
        }
        return APPLICATION_PATH . '/configs/ini/application.ini';
    }

    /**
     * @param mixed $configLocation
     */
    public static function setConfigLocation($configLocation)
    {
        self::$configLocation = $configLocation;
    }

    /**
     * @return null
     */
    public static function getMode()
    {
        if (defined(APPLICATION_ENV) && self::$mode == null) {
            self::$mode = APPLICATION_ENV;
        }
        if (self::$mode == null) {
            return self::DEFAULT_MODE;
        }
        return self::$mode;
    }

    /**
     * @param null $mode
     */
    public static function setMode($mode)
    {
        self::$mode = $mode;
    }

    public static function set($path, $value)
    {
        /** @var $config Zend_Config */
        $config = self::getConfig(false);

        if (strpos($path, '.')) {
            $path = explode('.', $path);
        } else {
            $path = array($path);
        }
        foreach ($path as $i => $key) {
            if ($i == count($path) - 1) {
                $config->$key = $value;
            } else {
                if (!isset($config->$key)) {
                    $config->$key = new Zend_Config(array(), true);
                }
                $config = $config->get($key, false);

            }
        }
    }


}