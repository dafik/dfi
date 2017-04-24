<?

namespace Dfi\Ldap;

use Dfi\App\Config;
use Dfi\Controller\Action\Helper\Messages;
use Dfi\Iface\Helper;
use Dfi\Iface\Model\Asterisk\AccountSip;
use Dfi\Iface\Provider\Pbx\AccountSipProvider;
use Dfi\Ldap;
use Exception;
use Zend_Auth_Adapter_Ldap;
use Zend_Config_Ini;
use Zend_Ldap_Attribute;
use Zend_Ldap_Collection;
use Zend_Ldap_Exception;
use Zend_Ldap_Filter;
use Zend_Ldap_Filter_And;
use Zend_Log;
use Zend_Log_Filter_Priority;
use Zend_Registry;

class AD
{

    /**
     * Singelton instance
     *
     * @var AD
     */
    private static $_instance;
    private $_connected = false;


    private $_ldap = array();

    private function __construct()
    {
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    /**
     * Singelton constructor
     *
     * @return AD
     */
    public static function getInstance()
    {
        if (self::$_instance instanceof AD) {
            return self::$_instance;
        }
        return self::$_instance = new AD();
    }

    /**
     *
     * @return array
     */
    public function test()
    {
        return AD::getInstance()->getUserByLogin('d.ni');
    }

    public static function getSipInfoByAD($login, $password)
    {
        $sipInfo = array(
            'sip' => 0,
            'ddi' => 0,
            'password' => '',
            'message' => ''
        );
        $auth = AD::getInstance()->auth($login, $password);
        if ($auth) {
            $user = AD::getInstance()->getUserByLogin($login);
            if ($user) {
                if (isset($user['telephonenumber']) && isset($user['telephonenumber'][0])) {
                    $sip = $user['telephonenumber'][0];
                    if ($sip) {
                        $sipInfo['sip'] = $sip;
                        /* @var $accountProvider AccountSipProvider */
                        $accountProvider = (Helper::getClass('iface.pbx.accountSip'))::create();
                        /* @var $account AccountSip */
                        $account = $accountProvider->filterByNumber($sip)->findOne();

                        if ($account) {
                            $sipInfo['ddi'] = $account->getDdiNumber();
                            $sipInfo['password'] = $account->getPassword();
                        } else {
                            $sipInfo['message'] = 'sip account not found in hr.dev';

                        }
                    }
                } else {
                    $sipInfo['message'] = 'sip account not assigned in AD';
                }
            } else {
                $sipInfo['message'] = 'AD account not found';
            }
        } else {
            $sipInfo['message'] = 'not authenticated';
        }
        return $sipInfo;
    }

    public static function getSipInfoByADLogin($login)
    {
        $sipInfo = array(
            'sip' => 0,
            'ddi' => 0,
            'message' => ''
        );
        $user = AD::getInstance()->getUserByLogin($login);
        if ($user) {
            if (isset($user['telephonenumber']) && isset($user['telephonenumber'][0])) {
                $sip = $user['telephonenumber'][0];
                if ($sip) {
                    $sipInfo['sip'] = $sip;

                    /** @var AccountSipProvider $accountProvider */
                    $accountProvider = (Helper::getClass('iface.pbx.accountSip'))::create();
                    /* @var $account AccountSip */
                    $account = $accountProvider->filterByNumber($sip)->findOne();
                    if ($account) {
                        $sipInfo['ddi'] = $account->getDdiNumber();
                    } else {
                        $sipInfo['message'] = 'sip account not found in hr.dev';
                    }
                }
            } else {
                $sipInfo['message'] = 'sip account not assigned in AD';
            }
        } else {
            $sipInfo['message'] = 'AD account not found';
        }
        return $sipInfo;
    }

    public static function getSipData($login, $password, $userLogin)
    {
        $sipInfo = array(
            'sip' => 0,
            'ddi' => 0,
            'password' => '',
            'message' => '',
            'isPjSip' => 0
        );
        $auth = AD::getInstance()->auth($login, $password);
        if ($auth) {
            $user = AD::getInstance()->getUserByLogin($userLogin);
            if ($user) {
                if (isset($user['telephonenumber']) && isset($user['telephonenumber'][0])) {
                    $sip = $user['telephonenumber'][0];
                    if ($sip) {
                        $sipInfo['sip'] = $sip;
                        /* @var $accountProvider AccountSipProvider */
                        $accountProvider = (Helper::getClass('iface.pbx.accountSip'))::create();

                        $account = $accountProvider->filterByNumber($sip)->findOne();
                        /* @var $account AccountSip */
                        if ($account) {
                            $sipInfo['ddi'] = $account->getDdiNumber();
                            $sipInfo['password'] = $account->getPassword();
                        } else {
                            $sipInfo['message'] = 'sip account not found in hr.dev';

                        }
                    }
                } else {
                    $sipInfo['message'] = 'sip account ' . $userLogin . 'not assigned in AD' . print_r($user, true);
                }
            } else {
                $sipInfo['message'] = 'AD account not found';
            }
        } else {
            $sipInfo['message'] = 'not authenticated';
        }
        return $sipInfo;
    }


    public function auth($username, $password)
    {
        try {

            $config = new Zend_Config_Ini('configs/ad-conf.ini', 'production');
            $options = $config->get('ldap')->get('servers')->toArray();

            $adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);
            $result = $adapter->authenticate();

            $messages = $result->getMessages();

            $logger = Zend_Registry::get('ADLogger');
            $filter = new Zend_Log_Filter_Priority(Zend_Log::DEBUG);
            $logger->addFilter($filter);

            foreach ($messages as $i => $message) {
                if ($i-- > 1) { // $messages[2] and up are log messages
                    $message = str_replace("\n", "\n  ", $message);
                    $logger->log("Ldap: $i: $message", Zend_Log::DEBUG);
                }
            }
            return $result->isValid();
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
            return false;
        }
    }

    public function updateByLogin($login, $options)
    {
        $filter = Zend_Ldap_Filter::equals('samaccountname', $login);
        $ldap = $this->getLdap();
        $users = $ldap->search($filter);
        if ($users->count() > 0) {
            $user = $users->getFirst();
            $dn = $user['dn'];
            $entry = array();
            foreach ($options as $attrib => $value) {
                Zend_Ldap_Attribute::setAttribute($entry, $attrib, $value);
            }
            $res = $ldap->update($dn, $entry);
            return $res;
        }
        return false;
    }

    public function resetPassword($login)
    {
        $options = array(
            'unicodePwd' => $this->encodeUnicodePassword(Config::get('ad.defaultPassword')),
            'pwdLastSet' => 0
        );
        $res = $this->updateByLogin($login, $options);
        return $res;
    }

    public function disableUser($login)
    {
        $user = $this->getUserByLogin($login);
        if ($user) {

            $accountControl = (int)$user['useraccountcontrol'][0];

            $options = array(
                'UserAccountControl' => ($accountControl | 2)
            );
            $res = $this->updateByLogin($login, $options);
            return $res;
        }
        return false;
    }

    public function enableUser($login)
    {
        $user = $this->getUserByLogin($login);
        if ($user) {

            $accountControl = (int)$user['useraccountcontrol'][0];

            $options = array(
                'UserAccountControl' => ($accountControl ^ 2)
            );
            $res = $this->updateByLogin($login, $options);
            return $res;
        }
        return false;
    }

    public function addUserToGroup($userDn, $groupDn)
    {

        $group_info['member'] = $userDn;
        $user = $this->getUserByDn($userDn);
        if (!$user) {
            return true;
        }

        $memberOf = $user['memberof'];
        if (array_search($groupDn, $memberOf)) {
            return true;
        }

        $isAdded = @ldap_mod_add($this->getLdap()->getResource(), $groupDn, $group_info);

        if ($isAdded === false) {
            /**
             * @see Zend_Ldap_Exception
             */
            throw new Zend_Ldap_Exception($this->getLdap(), 'adding: ' . $userDn);
        }
        return true;
    }

    public function removeUserFromGroup($userDn, $groupDn)
    {
        $group_info['member'] = $userDn;
        $user = $this->getUserByDn($userDn);

        if (!$user) {
            return true;
        }

        $memberOf = $user['memberof'];
        if (false === array_search($groupDn, $memberOf)) {
            return true;
        }


        $isDeleted = @ldap_mod_del($this->getLdap()->getResource(), $groupDn, $group_info);

        if ($isDeleted === false) {
            /**
             * @see Zend_Ldap_Exception
             */
            throw new Zend_Ldap_Exception($this->getLdap(), 'removing: ' . $userDn);
        }

        return true;
    }

    /**
     *
     * @param bool $baseDn
     * @param array $filterOptions
     * @param array $attr
     * @return Zend_Ldap_Collection
     * @throws Zend_Ldap_Exception
     * @internal param array $filter
     */

    public function getUsers($baseDn = true, $filterOptions = array(), $attr = array())
    {
        $filters = array();
        $filters[] = Zend_Ldap_Filter::equals('objectCategory', 'person');
        $filters[] = Zend_Ldap_Filter::equals('objectCategory', 'user');

        foreach ($filterOptions as $filter) {
            $filters[] = $filter;
        }


        $f = new Zend_Ldap_Filter_And($filters);

        $config = $this->getConfig();

        if ($baseDn) {
            $baseDn = $config['ad']['searchDn'];
        } else {
            $baseDn = array($config['ad']['baseDn']);
        }

        $ldap = $this->getLdap();

        if (count($attr) == 0) {
            $attributes = array(
                'displayname',
                'dn',
                'givenname',
                'name',
                'samaccountname',
                'sn',
                'whencreated',
                'useraccountcontrol',
                'memberof',
                'telephonenumber',
                'objectguid'
            );
        } else {
            $attributes = $attr;
        }

        $adUsers = array();
        foreach ($baseDn as $dn) {
            $res = $ldap->search($f, $dn, null, $attributes);
            $adUsers = array_merge($res->toArray(), $adUsers);
        }


        return $adUsers;
    }

    /**
     *
     * @return Zend_Ldap_Collection
     */

    public function getOus()
    {

        $f1 = Zend_Ldap_Filter::equals('objectCategory', 'organizationalUnit');

        $ldap = $this->getLdap();

        $attributes = array(
            'dn',
            'ou',
            'objectguid',
        );

        $ous = $ldap->search($f1, NULL, NULL, $attributes);

        return $ous;
    }

    /**
     *
     * @return Zend_Ldap_Collection
     */

    public function getGroups()
    {

        $f1 = Zend_Ldap_Filter::equals('objectCategory', 'group');

        $ldap = $this->getLdap();

        $attributes = array(
            'cn',
            'dn',
            'ou',
            'objectguid',
        );

        $groups = $ldap->search($f1, NULL, NULL, $attributes);

        return $groups;
    }


    public static function getDgmGroupAsArray($filterByShiftGroups = false)
    {

        $groups = AD::getInstance()->getGroups();

        $tmp = $groups->toArray();
        $groupsArray = array();
        foreach ($tmp as $ou) {
            if ($filterByShiftGroups) {
                if (false !== strpos($ou['dn'], 'OU=Groups') && false !== strpos($ou['dn'], 'OU=Zmiany')) {
                    $groupsArray[$ou['dn']] = $ou;
                    $groupsArray[$ou['dn']]['objectguid'] = bin2hex($ou['objectguid'][0]);
                }
            } else {
                if (false !== strpos($ou['dn'], 'OU=Groups') || false !== strpos($ou['dn'], 'OU=Global Groups')) {
                    $groupsArray[$ou['dn']] = $ou;
                    $groupsArray[$ou['dn']]['objectguid'] = bin2hex($ou['objectguid'][0]);
                }
            }
        }
        return $groupsArray;
    }


    /**
     * @param string $number
     * @return Zend_Ldap_Collection
     */
    public function getUserByPhoneNumber($number)
    {
        $f1 = Zend_Ldap_Filter::equals('telephoneNumber', $number);
        $ldap = $this->getLdap();
        $attributes = array(
            'displayname',
            'dn',
            'givenname',
            'name',
            'samaccountname',
            'sn',
            'telephoneNumber'
        );
        $users = $ldap->search($f1, null, null, $attributes);
        return $users;
    }

    public function getUserByLogin($login)
    {
        $f1 = Zend_Ldap_Filter::equals('objectCategory', 'person');
        $f2 = Zend_Ldap_Filter::equals('objectCategory', 'user');
        $f7 = Zend_Ldap_Filter::equals('samaccountname', $login);

        $f8 = Zend_Ldap_Filter::andFilter($f1, $f2);
        $f10 = Zend_Ldap_Filter::andFilter($f7, $f8);

        $ldap = $this->getLdap();

        $attributes = array(
            'displayname',
            'dn',
            'givenname',
            'name',
            'samaccountname',
            'sn',
            'whencreated',
            'useraccountcontrol',
            'memberof',
            'telephoneNumber',
            'objectguid'
        );

        $adUsers = $ldap->search($f10, null, null, $attributes);

        return $adUsers->getFirst();
    }

    public function getUserByDn($dn)
    {
        $f1 = Zend_Ldap_Filter::equals('distinguishedname', $dn);

        $ldap = $this->getLdap();

        $attributes = array(
            'displayname',
            'dn',
            'givenname',
            'name',
            'samaccountname',
            'sn',
            'whencreated',
            'useraccountcontrol',
            'memberof',
            'telephoneNumber',
            'objectguid'
        );

        $adUsers = $ldap->search($f1, null, null, $attributes);

        return $adUsers->getFirst();
    }


    public function getDetailsByLoginAndController($login, $controller)
    {
        try {
            $ldap = $this->getLdap($controller);
        } catch (Exception $e) {
            $x = 1;
        }


        if ($ldap) {

            $f1 = Zend_Ldap_Filter::equals('samaccountname', $login);


            /* attributes = array(
             'displayname',
             'dn',
             'givenname',
             'name',
             'samaccountname',
             'sn',
             'whencreated',
             'useraccountcontrol',
             'memberof',
             'telephoneNumber',
             'objectguid'
             ); */

            $adUsers = $ldap->search($f1);

            return $adUsers->getFirst();

        }
        return [];
    }


    public function deleteUserByLogin($adLogin)
    {
        $adUser = $this->getUserByLogin($adLogin);
        if (isset($adUser['dn'])) {
            return $this->getLdap()->delete($adUser['dn']);
        }
        return false;
    }


    /**
     *
     * @param null|string $controller
     * @return Ldap
     */
    private function getLdap($controller = null)
    {
        if ($controller) {
            if (!isset($this->_ldap[$controller])) {
                $this->connect($controller);
            }
            return $this->_ldap[$controller];
        } else {
            if (!$this->_connected) {
                $this->connect();
            }
            $ldap = $this->_ldap;
            return array_shift($ldap);
        }
    }

    private function getConfig()
    {
        $config = new Zend_Config_Ini('configs/ad-conf.ini', 'production');
        return $config->get('ldap')->toArray();
    }

    public function getControllerNames()
    {
        $config = $this->getConfig();

        $names = array();
        foreach ($config['servers'] as $controller => $value) {
            $names[$value['host']] = $controller;
        }

        return $names;
    }

    private function connect($controller = null)
    {
        $config = $this->getConfig();

        if ($controller) {
            $ldap = new Ldap();

            if (isset($config['servers'][$controller])) {
                $options = $config['servers'][$controller];

                $ldap->setOptions($options);
                try {
                    $ldap->bind();
                    $this->_ldap[$controller] = $ldap;
                    $this->_connected = true;
                    return true;
                } catch (Zend_Ldap_Exception $zle) {
                    throw $zle;
                }
            } else {
                throw new Exception('controller not found');
            }
        } else {
            $ldap = new Ldap();
            foreach ($config['servers'] as $name => $options) {

                $ldap->setOptions($options);
                try {
                    $ldap->bind();
                    $this->_ldap[$name] = $ldap;
                    $this->_connected = true;
                    return true;
                } catch (Zend_Ldap_Exception $zle) {
                    if ($zle->getCode() === Zend_Ldap_Exception::LDAP_SERVER_DOWN) {
                        continue;
                    }
                    throw $zle;
                }
            }
        }
        return false;
    }

    private function encodeUnicodePassword($password)
    {
        $newPassword = "\"" . $password . "\"";
        $newPass = mb_convert_encoding($newPassword, "UTF-16LE");
        return $newPass;
    }

    public function parseError($data)
    {


        return $data;
    }
}
