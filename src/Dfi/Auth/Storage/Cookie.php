<?php

namespace Dfi\Auth\Storage;

use DateTime;
use Dfi\Controller\Action\Helper\Messages;
use Dfi\Crypt\MCrypt;
use Dfi\Iface\Model\Sys\User;
use Exception;
use Zend_Auth_Storage_Exception;
use Zend_Auth_Storage_Interface;
use Zend_Controller_Front;

class Cookie implements Zend_Auth_Storage_Interface
{
    protected $userId;
    protected $user;
    protected $initialized = false;
    protected $headersSent = false;

    protected $model;


    /**
     * User model propel class name
     * @param string $model
     */
    function __construct($model)
    {
        $this->model = $model;
    }


    /**
     * Returns true if and only if storage is empty
     *
     * @throws Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty()
    {
        if (!$this->initialized) {
            $this->read();
        }
        if (null == $this->userId) {
            return true;
        }
        return false;
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws Zend_Auth_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */

    public function read()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request) {
            $controller = Zend_Controller_Front::getInstance()->getRequest()->getParam('controller');

        } else {
            $controller = '';

        }

        try {
            $this->initialized = true;

            if (isset($_COOKIE['_u']) && $_COOKIE['_u'] && $_COOKIE['_u'] != 'deleted') {

                $base = base64_decode($_COOKIE['_u']);
                $decrypted = MCrypt::decode($base);
                list($userId, $token) = explode('-', $decrypted);

                $time = time();

                if ($token + (20 * 60) >= $time) {

                    $queryClass = ucfirst($this->model) . 'Peer';
                    $user = $queryClass::retrieveByPK($userId);
                    /** @var $user User */
                    if ($user) {

                        $this->userId = $user->getPrimaryKey();
                        $this->user = $user;
                        return $user;
                    } else {
                        if (!in_array($controller, array('login', 'logout'))) {

                            Messages::getInstance()->addMessage(Messages::TYPE_DEBUG, 'bad cookie user');
                        }
                    }
                } else {
                    if (!in_array($controller, array('login', 'logout'))) {
                        Messages::getInstance()->addMessage(Messages::TYPE_DEBUG, 'cookie expired: ' . $base . ' dec: ' . $decrypted . ' token:' . $token . ' diff:' . ($time - $token) / 60);
                    }
                }
            } else {
                if (!in_array($controller, array('login', 'logout'))) {
                    //Messages::getInstance()->addMessage(Messages::TYPE_DEBUG, 'cookie auth not set');
                }
            }
        } catch (Exception $e) {
            throw new Zend_Auth_Storage_Exception($e->getMessage());
        }
        return false;
    }


    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws Zend_Auth_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $user = $contents;
        try {
            if (null === $user) {
                if ($this->user) {
                    $user = $this->user;
                } else {
                    return;
                }
            }

            if ($user) {
                $this->userId = $user->getPrimaryKey();
                if (!headers_sent()) {
                    $response = Zend_Controller_Front::getInstance()->getResponse();
                    $date = new DateTime();
                    $date->modify('+1200 seconds');
                    $response->setHeader('Set-Cookie', '_u = ' . base64_encode(MCrypt::encode($user->getPrimaryKey() . '-' . time())) . '; Expires=' . $date->format(DATE_COOKIE) . '; path = /');
                    $this->headersSent = true;
                } else {
                    headers_sent($file, $line);
                    throw new Exception ('headers have been sent, file: ' . $file . ' line: ' . $line);
                }
            }

        } catch (Exception $e) {
            throw new Zend_Auth_Storage_Exception($e->getMessage());
        }
    }


    /**
     * Clears contents from storage
     *
     * @throws Zend_Auth_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {

        try {

            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->setHeader('Set-Cookie', '_u = deleted ; Expires=Thu, 01 Jan 1970 00:00:01 GMT;path = /', true);

        } catch (Exception $e) {
            throw new Zend_Auth_Storage_Exception($e->getMessage());
        }


    }

}

