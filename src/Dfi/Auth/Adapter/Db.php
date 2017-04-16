<?php

namespace Dfi\Auth\Adapter;

use Dfi\App\Config;
use Dfi\Auth\Adapter\PasswordHasher\PasswordHasherInterface;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Sys\User;
use Dfi\Iface\Model\Sys\UserProvider;
use Exception;
use Zend_Auth_Result;

class Db implements AdapterInterface
{
    protected $_password;
    protected $_options;
    protected $_username;

    public function __construct(array $options = array(), $username = null, $password = null)
    {
        $options = Config::getConfig(true, 'main.auth');


        $this->setOptions($options);

        if ($username !== null) {
            $this->setUsername($username);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }

    public function setOptions($options)
    {
        $this->_options = is_array($options) ? $options : array();
        return $this;
    }

    /**
     * Returns the username of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Db Provides a fluent interface
     */
    public function setUsername($username)
    {
        $this->_username = (string)$username;
        return $this;
    }

    /**
     * Returns the password of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Db Provides a fluent interface
     */
    public function setPassword($password)
    {
        $this->_password = (string)$password;
        return $this;
    }


    public function authenticate()
    {
        $messages = array();
        $messages[0] = ''; // reserved
        $messages[1] = ''; // reserved

        $username = $this->_username;
        $password = $this->_password;

        if (!$username) {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $messages[0] = 'A username is required';
            return new Zend_Auth_Result($code, '', $messages);
        }
        if (!$password) {
            /* A password is required because some servers will
             * treat an empty password as an anonymous bind.
             */
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $messages[0] = 'A password is required';
            return new Zend_Auth_Result($code, '', $messages);
        }

        if (!isset($this->_options['table'])) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[0] = 'Table is required';
            return new Zend_Auth_Result($code, '', $messages);
        } else {
            try {
                $modelClass = Helper::getClass("iface.provider.sys.user");
                /** @var User $model */
                $model = new $modelClass;
            } catch (Exception $e) {
                $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
                $messages[0] = 'Table model not found';
                return new Zend_Auth_Result($code, '', $messages);
            }
        }

        if (!isset($this->_options['field']['login'])) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[0] = 'Table login field is required';
            return new Zend_Auth_Result($code, '', $messages);
        } else {
            $getter = 'get' . ucfirst($this->_options['field']['login']);
            if (!method_exists($model, $getter)) {
                $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
                $messages[0] = 'Table login field not exist';
                return new Zend_Auth_Result($code, '', $messages);
            }
        }

        if (!isset($this->_options['field']['password'])) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[0] = 'Table password field is required';
            return new Zend_Auth_Result($code, '', $messages);
        } else {
            $getter = 'get' . ucfirst($this->_options['field']['password']);
            if (!method_exists($model, $getter)) {
                $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
                $messages[0] = 'Table password field not exist';
                return new Zend_Auth_Result($code, '', $messages);
            }
        }


        if (!isset($this->_options['salt'])) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[0] = 'salt is required';
            return new Zend_Auth_Result($code, '', $messages);
        } else {
            $salt = $this->_options['salt'];
        }

        if (!isset($this->_options['hash'])) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[0] = 'hash  is required';
            return new Zend_Auth_Result($code, '', $messages);
        } else {
            $hash = $this->_options['hash'];
        }

        $providerName = Helper::getClass("iface.provider.sys.user");

        /** @var UserProvider $provider */
        $provider = $providerName::create();


        $obj = $provider->findOneByLogin($username);


        if (!$obj) {
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $messages[0] = 'user not found';
            return new Zend_Auth_Result($code, '', $messages);
        }

        /** @var PasswordHasherInterface $hasher */
        $hasher = $modelClass::getPasswordHasher();

        $sHash = $obj->$getter();
        $nHash = $hasher->hash($this->_password);

        if (is_resource($sHash)) {
            $sHash = stream_get_contents($sHash);
        }


        if ($sHash !== $nHash) {
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $messages[0] = 'bad password';
            return new Zend_Auth_Result($code, '', $messages);
        }


        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $obj, $messages);
    }

    public static function canChangePassword()
    {
        return true;
    }
}
