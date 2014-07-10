<?php

class Dfi_App_Config
{

    /**
     * @var  Zend_Config
     */
    private static $config;


    /**
     * @param bool $asArray
     * @param bool $path
     * @param mixed $default
     * @param string $mode
     * @return array|mixed|Zend_Config_Ini
     * @throws Exception
     */
    public static function getConfig($asArray = true, $path = false, $default = false, $mode = APPLICATION_ENV)
    {
        if (!self::$config instanceof Zend_Config) {
            if (Zend_Registry::isRegistered('appConfig')) {
                self::$config = Zend_Registry::get('appConfig');
                $config = self::$config;
            } else {

                if (self::hasSection(APPLICATION_PATH . '/configs/application.ini', $mode)) {

                    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $mode, array('allowModifications' => true));

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
                    throw new Exception('config path ' . implode('.', $path) . ' not found');
                }
            }
        }
        if ($asArray) {
            return $config->toArray();
        } else {
            return $config;
        }
    }


    /**
     * @param bool $path
     * @param bool $default
     * @param bool $asArray
     * @param string $mode
     * @return array|mixed|Zend_Config_Ini
     */
    public static function get($path = false, $default = false, $asArray = false, $mode = APPLICATION_ENV)
    {
        return self::getConfig($asArray, $path, $default, $mode);
    }

    public static function hasSection($file, $section)
    {
        $ini = parse_ini_file(APPLICATION_PATH . '/configs/application.ini', true);
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

}