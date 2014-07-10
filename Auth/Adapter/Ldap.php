<?php
class Dfi_Auth_Adapter implements Zend_Auth_Adapter_Interface
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

	public function __construct($username , $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	/**
	 * Performs an authentication attempt
	 *
	 * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
	 * @return Zend_Auth_Result
	 */
	public function authenticate()
	{
		try
		{
			$this->user = SysUserQuery::create()->filterByLogin($this->username)->findOne();
			if ($this->user){
				if($this->user->getActive()){
					$config = new Zend_Config_Ini('configs/ad-conf.php', 'production');
					$options = $config->ldap->servers->toArray();
					$adapter = new Zend_Auth_Adapter_Ldap($options,$this->username, $this->password);

					$result = $adapter->authenticate();

					if (Zend_Auth_Result::SUCCESS !==  $result->getCode() ){
						$m = Dfi_AD_DGM::getInstance()->parseError(array_pop($result->getMessages()));

						throw new Exception(($m ? $m : self::WRONG_PW));
					}

				}else{
					throw new Exception(self::NOT_ACTIVATE);
				}
			} else {
				throw new Exception(self::NOT_FOUND);
			}
		}catch (Exception $e)
		{
			$m = $e->getMessage();
			$z = strpos($m,'Invalid credentials');
			
			if (false !=  strpos($e->getMessage(),'Invalid credentials') ) {
				return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, self::BAD_PW_MESSAGE);
			}
			if ($e->getMessage() == self::WRONG_PW){
				return $this->result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, self::BAD_PW_MESSAGE);
			}elseif ($e->getMessage() == self::NOT_FOUND){
				return $this->result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, self::NOT_FOUND_MESSAGE);
			}elseif ($e->getMessage() == self::NOT_ACTIVATE){
				return $this->result(Zend_Auth_Result::FAILURE, self::NOT_ACTIVE_MESSAGE);
			}else{
				throw $e;
			}

		}
		return $this->result(Zend_Auth_Result::SUCCESS);
	}

	/**
	 * Factory for Zend_Auth_Result
	 *
	 *@param integer    The Result code, see Zend_Auth_Result
	 *@param mixed      The Message, can be a string or array
	 *@return Zend_Auth_Result
	 */
	public function result($code, $messages = array()) {
		if (!is_array($messages)) {
			$messages = array($messages);
		}

		return new Zend_Auth_Result(
		$code,
		$this->user,
		$messages
		);
	}
	private function authAd() {
		try {
			$username = $this->_request->getParam('login');
			$password = $this->_request->getParam('password');

			$user = SysUserPeer::retrieveByAdLogin($username);

			if ($user instanceof SysUser) {
				$this->logUser = $user;

				$auth = Zend_Auth::getInstance();
				$config = new Zend_Config_Ini('configs/ad-conf.php', 'production');
				$options = $config->ldap->toArray();
				$adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);

				$result = $auth->authenticate($adapter);

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
			}
			return false;
		} catch (Exception $e) {
			$this->messages->addMessage('debug', $e->getMessage());
			return false;
		}
	}
}
