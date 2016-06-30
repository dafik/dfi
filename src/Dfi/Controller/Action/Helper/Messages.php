<?

class Dfi_Controller_Action_Helper_Messages extends Zend_Controller_Action_Helper_Abstract
{
    const TYPE_ERROR = 'error';
    const TYPE_NOTICE = 'notice';
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_DEBUG = 'debug';

    /**
     * Layout
     *
     * @var Zend_Layout
     */
    private $layout;
    /**
     * View
     *
     * @var Zend_View_Interface
     */
    private $view;
    /**
     * Dfi_Controller_Action_Helper_Messages
     *
     * @var Dfi_Controller_Action_Helper_Messages
     */
    private static $instance;
    /**
     * Types of messages
     *
     * @var array
     */
    private $types = array(self::TYPE_ERROR, self::TYPE_NOTICE, self::TYPE_CONFIRMATION, self::TYPE_DEBUG);


    private $messages = array();
    private $messagesDict = array();

    private $messagesExist = false;

    private function __construct()
    {
        parent::init();

        $this->view = new Zend_View();


        foreach ($this->types as $type) {
            $this->messages[$type] = array();
            if ($type != self::TYPE_DEBUG) {

                /** @noinspection PhpIncludeInspection */
                $this->messagesDict[$type] = include(APPLICATION_PATH . '/configs/messages/' . $type . '.php');
            }
        }
        $this->readFromSession();
    }

    /**
     * Enter description here...
     *
     * @return DFi_Controller_Action_Helper_Messages
     */
    public static function getInstance()
    {
        $x = self::$instance;

        if (!self::$instance instanceof Dfi_Controller_Action_Helper_Messages) {
            self::$instance = new Dfi_Controller_Action_Helper_Messages();
        }
        return self::$instance;
    }

    public function postDispatch()
    {
        $this->render();
        $this->save();
    }

    /**
     * retrive layout from mvc instance
     *
     * @return Zend_Layout
     */
    private function getLayout()
    {
        return Zend_Layout::getMvcInstance();
    }

    private function render()
    {
        if ($this->messagesExist) {
            $this->layout = $this->getLayout();

            $messages = $this->messages;

            $tmp = [];
            foreach ($messages as $messageType => $values) {
                if (count($values) > 0) {
                    if ($messageType == self::TYPE_CONFIRMATION) {
                        $messageType = 'success';
                    } elseif ($messageType == self::TYPE_DEBUG) {
                        $messageType = 'information';
                    } elseif ($messageType == self::TYPE_NOTICE) {
                        $messageType = 'alert';
                    }
                    $tmp[$messageType] = $values;
                }
            }
            $this->layout->assign('messages', json_encode($tmp));
            $this->resetMessages();
        }
    }

    public function addMessage($type, $message, $params = null)
    {
        if ($message) {
            if (!in_array($type, $this->types)) {
                throw new Exception('unknown message type');
            }
            if ($type != self::TYPE_DEBUG && isset($this->messagesDict[$type][$message])) {
                $this->messages[$type][] = self::parseMessage($this->messagesDict[$type][$message], $params);
            } elseif ($type == self::TYPE_DEBUG) {
                if (defined('APPLICATION_ENV') and APPLICATION_ENV == 'development') {
                    $message = self::parseMessage($message, $params);
                    if (false === array_search($message, $this->messages[$type])) {
                        $this->messages[$type][] = $message;
                    }
                }
            } else {
                throw new Exception('can\'t found message : ' . $message . ' in ' . $type);
            }
            $this->messagesExist = true;
        }
    }

    public function getMessage($type, $message, $params = null)
    {
        if (!in_array($type, $this->types)) {
            throw new Exception('unknown message type');
        }
        if ($type != self::TYPE_DEBUG && isset($this->messagesDict[$type][$message])) {
            return self::parseMessage($this->messagesDict[$type][$message], $params);
        } elseif ($type == self::TYPE_DEBUG) {
            if (defined('APPLICATION_ENV') and APPLICATION_ENV == 'development') {
                return self::parseMessage($message, $params);
            }
        } else {
            throw new Exception('can\'t found message : ' . $message . ' in ' . $type);
        }
        return false;
    }

    private function readFromSession()
    {
        $value = false;
        $x = new \Zend_Controller_Request_Http();
        //$value = $x->getHeader('X-message');


        if (isset($_COOKIE['_m']) && $_COOKIE['_m']) {
            $value = $_COOKIE['_m'];
        }
        if ($value) {
            $decoded = base64_decode($value);
            if ($decoded) {
                $jsoned = @json_decode($decoded, true);
                if (false === $jsoned) {
                    $unserialize = @unserialize($decoded);
                } else {
                    $unserialize = false;
                }
                if ($unserialize) {  //probaly very old way
                    /** @var Zend_Log $logger */
                    $logger = Zend_Registry::get('debugLogger');
                    $logger->log(Zend_Debug::dump(debug_backtrace(), 'unserialize', false), Zend_Log::DEBUG);
                    $messeges = $unserialize;
                    if (is_array($messeges)) {
                        $this->messages = array_merge($this->messages, $messeges);
                        $this->messagesExist = true;
                    }
                } elseif ($jsoned) {
                    $messeges = $jsoned;
                    if (is_array($messeges)) {
                        $this->messages = array_merge($this->messages, $messeges);
                        $this->messagesExist = true;
                    }
                } else {
                    Zend_Registry::get('debugLogger')->log('unable unserialize: ' . $decoded, Zend_Log::DEBUG);
                    //$this->getResponse()->setHeader('Set-Cookie', '_m = deleted ; path = / ; max-age = -3600');
                }
            }
        }
    }

    public function save()
    {
        $return = array();
        foreach ($this->messages as $type => $elems) {
            if (count($elems) > 0) {
                $return[$type] = $elems;
            }
        }
        if (count($return) > 0) {

            $mess = urlencode(base64_encode(json_encode($return)));
            //setcookie('_m',$mess);
            if ($mess) {
                $response = $this->getResponse()->setHeader('Set-Cookie', '_m = ' . $mess . ';path = /; expires= ' . date('r', time() + 3600));
                //$response = $this->getResponse()->setHeader('X-message', $mess);
            }
        }
    }

    public function resetMessages()
    {
        foreach ($this->messages as &$messagesType) {
            $messagesType = array();
        }
        if (isset($_COOKIE['_m']) && $_COOKIE['_m']) {
            //setcookie('_m','');
            $this->getResponse()->setHeader('Set-Cookie', '_m = deleted ; path = / ; expires= ' . date('r', time() - 3600));
        }
    }

    private function parseMessage($content, $params)
    {
        $matches = array();
        $res = preg_match_all('/%[a-zA-Z]+%/', $content, $matches);
        foreach ($matches[0] as $match) {
            if (isset($params[str_replace('%', '', $match)])) {
                $content = str_replace($match, $params[str_replace('%', '', $match)], $content);
            }
        }
        return $content;
    }

    public function getMessagesExist()
    {
        return $this->messagesExist;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}