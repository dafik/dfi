<?php

namespace Dfi\Auth\Adapter;

use Dfi\Ldap as DfiLdap;
use Zend_Auth_Adapter_Ldap;
use Zend_Ldap;
use Zend_Ldap_Filter;
use Zend_Translate;

class LdapPass extends Zend_Auth_Adapter_Ldap implements AdapterInterface
{

    /**
     * Returns the LDAP Object
     *
     * @return DfiLdap The Zend_Ldap object used to authenticate the credentials
     */
    public function getLdap()
    {
        if ($this->_ldap === null) {
            /**
             * @see Zend_Ldap
             */
            // require_once 'Zend/Ldap.php';
            $this->_ldap = new DfiLdap();
        }

        return $this->_ldap;
    }

    public static function canChangePassword()
    {
        return true;
    }

    public function __construct(array $options = array(), $username = null, $password = null)
    {
        $options = DfiLdap\Config::getConfig(true, 'ldap.servers');
        parent::__construct($options, $username, $password); // TODO: Change the autogenerated stub
    }


    public function changePassword($current, $new)
    {
        //$this->_password = $password;

        $filter = Zend_Ldap_Filter::equals('samaccountname', $this->_username);


        $config = DfiLdap\Config::getConfig(false, 'ldap')->toArray();
        $ldap = new \Dfi\Ldap(array_merge(array_shift($config['servers']), ['username' => $config['username'], 'password' => $config['password']]));

        $users = $ldap->search($filter);
        if ($users->count() == 1) {
            $user = $users->getFirst();
            $dn = $user['dn'];
            $entry = [
                [
                    "attrib" => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_REMOVE,
                    "values" => [$this->encodeUnicodePassword($current)],
                ],
                [
                    "attrib" => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_ADD,
                    "values" => [$this->encodeUnicodePassword($new)],
                ],
            ];

            $res = ldap_modify_batch($ldap->getResource(), $dn, $entry);
            if (!$res) {
                $em = ldap_error($ldap->getResource());
                $ec = ldap_errno($ldap->getResource());
                if ($ec == \Zend_Ldap_Exception::LDAP_CONSTRAINT_VIOLATION) {
                    $pwdLastSet = date_create("@" . round($user['pwdlastset'][0] / 10000000) - 11644477200);
                    $dateAllow = new \DateTime();
                    $dateAllow->modify('-1 day');

                    if ($pwdLastSet > $dateAllow) {
                        throw new Exception(\Zend_Registry::get('translator')->translate('_exception.pwdSetToEarly'), $ec);
                    } else {
                        throw new Exception(\Zend_Registry::get('translator')->translate('_exception.constrainViolation'), $ec);
                    }
                } else {
                    $le = new \Zend_Ldap_Exception($ldap, 'updating: ' . $dn);
                    throw new Exception($em, $ec, $le);
                }


            }
            return $res;
        } else {
            throw new Exception($ldap, \Zend_Registry::get('translator')->translate('_exception.accountNotFound'));
        }
        return false;

    }

    private function encodeUnicodePassword($password)
    {
        $newPassword = "\"" . $password . "\"";
        $newPass = mb_convert_encoding($newPassword, "UTF-16LE");
        return $newPass;
    }

    public function setTranslator(Zend_Translate $translator)
    {
        $this->translator = $translator;
    }

    public function authenticate()
    {
        $result = parent::authenticate();
        if ($result->getCode() < 1) {
            AdExtendedCodes::checkExtended($result);
        }

        return $result;
    }

    public static function isUsingPasswordHasher()
    {
        return false;
    }

    public static function canChangePasswordBySelf()
    {
        return true;
    }
}
