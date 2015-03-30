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
     * @return Dfi_Controller_Action_Helper_Messages
     */
    public static function getInstance()
    {

        if (!self::$instance instanceof Dfi_Controller_Action_Helper_Messages) {
            self::$instance = new Dfi_Controller_Action_Helper_Messages();
        }
        return self::$instance;
    }

    public function postDispatch()
    {
        $this->render();
    }

    /**
     * retrieve layout from mvc instance
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

    /** @noinspection PhpInconsistentReturnPointsInspection */
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
    }

    private function readFromSession()
    {
        if (isset($_COOKIE['_m']) && $_COOKIE['_m']) {
            //TODO
            //2014-05-15T19:16:13+02:00 ERR (3): /srv/app/lillalou-teens.pl/web/library/Ext/Controller/Action/Helper/Messages.php: (139) : unserialize(): Error at offset 0 of 5 bytes
            //2014-05-15T19:16:13+02:00 ERR (3): /srv/app/lillalou-teens.pl/web/library/Ext/Controller/Action/Helper/Messages.php: (140) : array_merge(): Argument #2 is not an array
            //2014-05-15T19:16:13+02:00 ERR (3): /srv/app/lillalou-teens.pl/web/library/Ext/Controller/Action/Helper/Messages.php: (164) : Invalid argument supplied for foreach()

            //$messages = unserialize(base64_decode($_COOKIE['_m']));
            //$this->messages = array_merge($this->messages, $messages);
            //$this->messagesExist = true;

            $x = $_COOKIE['_m'];

            $decoded = base64_decode($x);
            if ($decoded) {
                $unSerialize = @unserialize($decoded);
                if ($unSerialize) {
                    $messages = @unserialize(base64_decode($_COOKIE['_m']));
                    if (is_array($messages)) {
                        $this->messages = array_merge($this->messages, $messages);
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
        foreach ($this->messages as $type => $elements) {
            if (count($elements) > 0) {
                $return[$type] = $elements;
            }
        }
        if (count($return) > 0) {
            $mess = urlencode(base64_encode(serialize($return)));

            if ($mess) {
                $date = new DateTime();
                $date->modify('+60 seconds');
                $this->getResponse()->setHeader('Set-Cookie', '_m = ' . $mess . ';path = /; Expires=' . $date->format(DATE_COOKIE));
            }
        }
    }

    public function resetMessages()
    {
        foreach ($this->messages as &$messagesType) {
            $messagesType = array();
        }
        if (isset($_COOKIE['_m']) && $_COOKIE['_m']) {
            $date = new DateTime();
            $date->modify('-10 seconds');
            $this->getResponse()->setHeader('Set-Cookie', '_m = deleted ; path = / ; Expires=' . $date->format(DATE_COOKIE));
        }
    }

    private function parseMessage($content, $params)
    {
        /** @var $helper Zend_View_Helper_Translate */
        $helper = $this->view->getHelper('translate');

        $content = $helper->translate($content);

        $matches = array();
        preg_match_all('/%[a-zA-Z]+%/', $content, $matches);
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