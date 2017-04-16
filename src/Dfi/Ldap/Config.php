<?php
namespace Dfi\Ldap;

use Exception;
use Zend_Config;
use Zend_Config_Ini;

class Config
{
    public static function getConfig($asArray = true, $path = false, $default = null, $mode = APPLICATION_ENV)
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ini/ldap.ini', $mode);

        if ($path) {
            if (strpos($path, '.')) {
                $path = explode('.', $path);
            } else {
                $path = array($path);
            }
            foreach ($path as $key) {
                $config = $config->get($key, $default);
                if (!$config instanceof Zend_Config) {
                    throw new Exception('config path ' . $path . ' not found');
                }
            }
        }
        if ($asArray) {
            return $config->toArray();
        } else {
            return $config;
        }
    }

}