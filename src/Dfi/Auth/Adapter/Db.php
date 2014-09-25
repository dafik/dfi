<?php

class Dfi_Auth_Adapter_Db implements Zend_Auth_Adapter_Interface
{
    const NOT_FOUND_MESSAGE = "Account not found";
    const BAD_PW_MESSAGE = "Password is invalid";
    const NOT_ACTIVE_MESSAGE = "Account not active";
    const NOT_FOUND = "Podane błędne login lub hasło";
    const WRONG_PW = "Podane błędne hasło";
    const NOT_ACTIVATE = "Użytkownik nieaktywny";


    /**
     *
     * @var Model_User
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


    protected $modelName;
    protected $hashMethod;
    protected $activityField;

    public function __construct($username, $password, $options)
    {
        $this->username = $username;
        $this->password = $password;

        $this->options = $options;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Exception
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {


        try {
            $queryClass = $this->options['table'] . 'Query';

            $this->user = $queryClass::create()
                ->filterBy(ucfirst($this->options['loginField']), $this->username)
                ->findOne();
            if ($this->user) {
                if ($this->options['activityField']) {
                    $activityMethod = 'get' . $this->options['activityField'];
                    if (!$this->user->$activityMethod()) {
                        throw new Exception(self::NOT_ACTIVATE);
                    }
                }

                $passwordMethod = 'get' . ucfirst($this->options['passwordField']);
                $hashMethod = $this->options['passwordHash'];


                if ($hashMethod == 'plain') {
                    $encodedPassword = $this->password;
                } else {
                    $encodedPassword = call_user_func($hashMethod, $this->password);
                }

                if ($this->user->$passwordMethod() != $encodedPassword) {
                    throw new Exception(self::WRONG_PW);
                }


            } else {
                throw new Exception(self::NOT_FOUND);
            }
        } catch (Exception $e) {
            if (false != strpos($e->getMessage(), 'Invalid credentials')) {
                return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, self::BAD_PW_MESSAGE);
            }
            if ($e->getMessage() == self::WRONG_PW) {
                return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, self::BAD_PW_MESSAGE);
            } elseif ($e->getMessage() == self::NOT_FOUND) {
                return $this->result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, self::NOT_FOUND_MESSAGE);
            } elseif ($e->getMessage() == self::NOT_ACTIVATE) {
                return $this->result(Zend_Auth_Result::FAILURE, self::NOT_ACTIVE_MESSAGE);
            } else {
                throw $e;
            }

        }
        return $this->result(Zend_Auth_Result::SUCCESS);
    }

    /**
     * Factory for Zend_Auth_Result
     *
     * @param integer $code   The Result code, see Zend_Auth_Result
     * @param mixed $messages     The Message, can be a string or array
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
}
