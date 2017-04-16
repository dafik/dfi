<?php
namespace Dfi\Auth;

use Dfi\App\Config;
use Dfi\Auth\Adapter\AdapterInterface;
use Dfi\Controller\Action\Helper\Messages;
use Dfi\Iface\Model\Sys\User;
use Dfi\Iface\Model\Sys\UserProvider;
use Exception;
use Zend_Auth_Adapter_Interface;
use Zend_Auth_Result;
use Zend_Translate;

class Adapter implements Zend_Auth_Adapter_Interface
{
//    const NOT_FOUND_MESSAGE = "Account not found";
//    const BAD_PW_MESSAGE = "Password is invalid";
    const NOT_ACTIVE_MESSAGE = "Account not active";
    const NOT_FOUND = "login.error.notFound";
    const WRONG_PW = "login.error.badPassword";
    const NOT_ACTIVATE = "login.error.accountNotActive";
    const FOREIGNER_NETWORK = "login.error.foreignerNetwork";


    /**
     *
     * @var User
     */
    protected $user;

    /**
     *
     * @var string
     */
    protected $username;

    /**
     *
     * @var string
     */
    protected $password;
    protected $useFakeLogin = false;

    /**
     * @var Zend_Translate
     */
    protected $translator;

    /**
     * @var UserProvider
     */
    protected $provider;

    public function __construct(UserProvider $provider, $username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->provider = $provider;

        if (Config::get('main.useFakeLogin')) {
            $this->useFakeLogin = true;
        }
    }

    /**
     * Performs an authentication attempt
     * @return Zend_Auth_Result If authentication cannot be performed
     * @throws Exception
     */
    public function authenticate()
    {
        try {
            $this->user = $this->provider->findByByLogin($this->username);
            if ($this->user) {
                if ($this->user->getActive()) {
                    if (!$this->useFakeLogin) {
                        $adapter = $this->getAdapter($this->user);
                        $result = $adapter->authenticate();
                        if (Zend_Auth_Result::SUCCESS !== $result->getCode()) {
                            $m = array_pop($result->getMessages());
                            Messages::getInstance()->addMessage('debug', $m);
                            throw new Exception(($m ? $m : $this->getMessage(self::WRONG_PW)));
                        }
                    }
                } else {
                    throw new Exception(self::NOT_ACTIVATE);
                }
            } else {
                throw new Exception($this->getMessage(self::NOT_FOUND));
            }
        } catch (Exception $e) {
            $m = $e->getMessage();
            //$z = strpos($m, 'Invalid credentials');

            if (false !== strpos($e->getMessage(), $this->getMessage(self::NOT_FOUND))) {
                return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->getMessage(self::NOT_FOUND));
            }
            if (false !== strpos($e->getMessage(), 'Invalid credentials')) {
                return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->getMessage(self::WRONG_PW));
            }
            if ($e->getMessage() == $this->getMessage(self::WRONG_PW)) {
                return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->getMessage(self::WRONG_PW));
            } elseif ($e->getMessage() == $this->getMessage(self::NOT_FOUND)) {
                return $this->result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $this->getMessage(self::NOT_FOUND));
            } elseif ($e->getMessage() == $this->getMessage(self::NOT_ACTIVATE)) {
                return $this->result(Zend_Auth_Result::FAILURE, $this->getMessage(self::NOT_ACTIVE_MESSAGE));
            } else {
                throw $e;
            }

        }
        return $this->result(Zend_Auth_Result::SUCCESS);
    }

    /**
     * @param User $user
     * @return AdapterInterface
     * @throws Exception
     */
    private function getAdapter(User $user)
    {
        $adapter = $user->getAuthAdapter();
        $adapter->setPassword($this->password);

        return $adapter;
    }


    /**
     * Factory for Zend_Auth_Result
     *
     * @param integer $code The Result code, see Zend_Auth_Result
     * @param mixed $messages The Message, can be a string or array
     * @return Zend_Auth_Result
     */
    public function result($code, $messages = array())
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        return new Zend_Auth_Result(
            $code,
            $this->user,
            $messages
        );
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    private function getMessage($message)
    {
        if ($this->translator) {
            return $this->translator->translate($message);
        } else {
            return $message;
        }
    }
}