<?php

namespace Dfi\Auth\Adapter;

use Dfi\Iface\Model\Sys\User;
use Zend_Auth_Result;
use Zend_Translate;

class Fake implements AdapterInterface
{


    public static function canChangePassword()
    {
        return false;
    }

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function changePassword($currentPassword, $newPassword)
    {
        return false;
    }

    public function setTranslator(Zend_Translate $translator)
    {
        $this->translator = $translator;
    }

    public function authenticate()
    {
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->user);
    }

    public static function isUsingPasswordHasher()
    {
        return false;
    }

    public static function canChangePasswordBySelf()
    {
        return false;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setUsername($login)
    {
        // TODO: Implement setUsername() method.
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        // TODO: Implement setPassword() method.
    }
}
