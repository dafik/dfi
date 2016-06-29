<?php

class Dfi_Auth_Adapter_Ldap extends Zend_Auth_Adapter_Ldap implements Dfi_Auth_Adapter_AdapterInterface
{

    /**
     * Returns the LDAP Object
     *
     * @return Zend_Ldap The Zend_Ldap object used to authenticate the credentials
     */
    public function getLdap()
    {
        if ($this->_ldap === null) {
            /**
             * @see Zend_Ldap
             */
            // require_once 'Zend/Ldap.php';
            $this->_ldap = new Dfi_Ldap();
        }

        return $this->_ldap;
    }

    public static function canChangePassword()
    {
        return false;
    }

    public function __construct(array $options = array(), $username = null, $password = null)
    {
        $options = Dfi_Ldap_Config::getConfig(true, 'ldap.servers');
        parent::__construct($options, $username, $password); // TODO: Change the autogenerated stub
    }


    public function setPassword($password)
    {
        $this->_password = $password;
    }
}
