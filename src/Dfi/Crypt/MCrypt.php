<?php

namespace Dfi\Crypt;

use Bootstrap;
use Exception;
use Zend_Config;
use Zend_Registry;
use Zend_View;

class MCrypt implements CryptInterface
{
    const EXTENSION = 'mcrypt';
    const CRYPT = 'crypt';

    const SECRET_KEY = 'secretKey';
    const CIPHER = 'cipher';
    const MODE = 'mode';

    const TR_EXCEPTION = '_exception.mcrypt.notFound';


    private static $secretKey;
    private static $cipher;
    private static $mode;


    public static function encode($string)
    {
        if (extension_loaded(self::EXTENSION) == true) {
            self::loadConfig();

            $keySize = mcrypt_get_key_size(self::$cipher, self::$mode);
            $key = substr(self::$secretKey, 0, $keySize);
            $value = trim($string);
            $vectorSize = mcrypt_get_iv_size(self::$cipher, self::$mode);
            $vector = mcrypt_create_iv($vectorSize, MCRYPT_RAND);
            $encrypted = mcrypt_encrypt(self::$cipher, $key, $value, self::$mode, $vector);
            $base = $vector . $encrypted;
            $output = base64_encode($base);
            $output = str_replace(array('+', '/', '='), array('-', '_', ''), $output);

            return $output;

        } else {
            $view = new Zend_View();
            $message = $view->translate(self::TR_EXCEPTION);
            throw new Exception($message);
        }

    }

    public static function decode($string)
    {

        if (extension_loaded(self::EXTENSION) == true) {
            self::loadConfig();


            $string = str_replace(array('-', '_'), array('+', '/'), $string);
            $base = base64_decode($string);
            $keySize = mcrypt_get_key_size(self::$cipher, self::$mode);
            $key = substr(self::$secretKey, 0, $keySize);
            $vectorSize = mcrypt_get_iv_size(self::$cipher, self::$mode);
            $vector = substr($base, 0, $vectorSize);
            $value = substr($base, $vectorSize);

            $decrypted = mcrypt_decrypt(self::$cipher, $key, $value, self::$mode, $vector);

            $decrypted = rtrim($decrypted, "\0");

            return $decrypted;

        } else {
            $view = new Zend_View();
            $message = $view->translate(self::TR_EXCEPTION);
            throw new Exception($message);
        }
    }

    private static function loadConfig()
    {
        if (null == self::$cipher || null == self::$secretKey || null == self::$mode) {

            self::$cipher = MCRYPT_TRIPLEDES;
            self::$mode = MCRYPT_MODE_ECB;

            $keySize = mcrypt_get_key_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
            self::$secretKey = str_pad('secret_key', $keySize, '_');


            if (class_exists('Bootstrap')) {
                $configRegistryKey = Bootstrap::CONFIG_KEY;

                if (Zend_Registry::isRegistered($configRegistryKey)) {
                    $config = Zend_Registry::get($configRegistryKey);
                    if ($config instanceof Zend_Config) {
                        $config = $config->toArray();
                    }
                    if (isset($config[self::CRYPT])) {
                        $config = $config[self::CRYPT];
                        self::$secretKey = $config[self::SECRET_KEY];
                        self::$cipher = $config[self::CIPHER];
                        self::$mode = $config[self::MODE];
                    }
                }
            }
        }
    }
}
