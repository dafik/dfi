<?php

namespace Dfi\Auth\Adapter;


use Zend_Auth_Adapter_Interface;
use Zend_Translate;

interface  AdapterInterface extends Zend_Auth_Adapter_Interface
{


    /**
     * @param string $login
     * @return $this
     */
    public function setUsername($login);

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password);


    /**
     * @return boolean
     */
    public static function canChangePassword();

    /**
     * @return boolean
     */
    public static function canChangePasswordBySelf();

    /**
     * @return bool
     */
    public static function isUsingPasswordHasher();

    /**
     * @param string $currentPassword
     * @param string $newPassword
     * @return boolean
     */
    public function changePassword($currentPassword, $newPassword);

    public function setTranslator(Zend_Translate $translate);

}