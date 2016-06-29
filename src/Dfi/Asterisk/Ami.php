<?
use PAMI\Autoloader\Autoloader;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\CommandAction;
use PAMI\Message\OutgoingMessage;

Autoloader::register();

class Dfi_Asterisk_Ami
{
    /**
     * AMI Client object
     * @var Dfi_Asterisk_Client
     */
    private static $amiClient;

    public static function reload()
    {
        try {
            $client = self::getAmiClient();
            if (!Dfi_App_Config::getString('asterisk.fake', true)) {
                $client->send(new CommandAction('reload'));
            }
        } catch (Exception $e) {
            Dfi_Controller_Action_Helper_Messages::getInstance()->addMessage('debug', $e->getMessage());
        }
    }

    public static function reloadDialplan()
    {
        try {
            $client = self::getAmiClient();
            if (!Dfi_App_Config::getString('asterisk.fake', true)) {
                $client->send(new CommandAction('dialplan reload'));
            }
        } catch (Exception $e) {
            Dfi_Controller_Action_Helper_Messages::getInstance()->addMessage('debug', $e->getMessage());
        }
    }

    /**
     * @param OutgoingMessage $message
     * @return PAMI\Message\Response\ResponseMessage
     */
    public static function send(OutgoingMessage $message)
    {
        try {
            $client = self::getAmiClient();
            if (defined('_ASTERISK_FAKE') && !_ASTERISK_FAKE) {
                $res = $client->send($message);
                //sleep(5);
                return $res;
            }
            return true;
        } catch (Exception $e) {
            Dfi_Controller_Action_Helper_Messages::getInstance()->addMessage('debug', $e->getMessage());
            return false;
        }
    }

    public static function getDialplans()
    {

        $res = self::send(new \PAMI\Message\Action\CommandAction('dialplan show'));
        $found = array();

        if ($res) {

            $lines = $res->getRawContent();

            $out = explode("\n", $lines);
            $found = array();
            $matches = array();
            $context = false;
            $extension = '';
            foreach ($out as $line) {
                if (preg_match('/^\[\s+Context\s\'(.+)\'\screa.+\]$/', $line, $matches)) {
                    $context = trim($matches[1]);
                    $found[$context] = array();
                    if ($context == 'fax') {
                        //TODO fax
                    }
                } elseif ($context) {
                    $line = trim($line);
                    if ($line) {
                        $line = preg_replace('/\[pbx_config\]/', '', $line);
                        $line = preg_replace('/\s\s+/', ' ', $line);
                        $line = trim($line);
                        if (preg_match('/^-=|^--/', $line)) {
                            //TODO
                        } elseif (preg_match('/^Include\s+=>\s\'(.+)\'/', $line, $matches)) {
                            $extension = 'Include';
                            $line = trim($matches[1]);
                            $found[$context][$extension][] = $line;
                        } elseif (preg_match('/^\'(.+)\'\s+=>\s+(.+)/', $line, $matches)) {
                            $extension = $matches[1];
                            $line = trim($matches[2]);
                            $found[$context][$extension][] = preg_replace('/^\d+\.\s+/', '', $line);
                        } else {
                            if (preg_match('/^\[(.+)\](.+)/', $line, $matches)) {
                                $label = $matches[1];
                                $line = trim($matches[2]);
                                $found[$context][$extension][] = array($label, preg_replace('/^\d+\.\s+/', '', $line));
                            } else {
                                $found[$context][$extension][] = preg_replace('/^\d+\.\s+/', '', $line);
                            }
                        }
                    }
                }
            }
            unset($found['parkedcalls'], $found['default']);

        }
        return $found;
    }

    public static function getDialplan($name)
    {

        $res = Dfi_Asterisk_Ami::send(new \PAMI\Message\Action\CommandAction('dialplan show ' . $name));

        $lines = $res->getRawContent();

        $out = explode("\n", $lines);
        $found = array();
        $matches = array();
        $context = false;
        $extensions = '';
        foreach ($out as $line) {
            if (preg_match('/^\[\s+Context\s\'(.+)\'\screa.+\]$/', $line, $matches)) {
                $context = trim($matches[1]);
                $found[$context] = array();
                if ($context == 'fax') {
                    //TODO
                }
            } elseif ($context) {
                $line = trim($line);
                if ($line) {
                    $line = preg_replace('/\[pbx_config\]/', '', $line);
                    $line = preg_replace('/\s\s+/', ' ', $line);
                    $line = trim($line);
                    if (preg_match('/^-=|^--/', $line)) {
                        //TODO
                    } elseif (preg_match('/^Include\s+=>\s\'(.+)\'/', $line, $matches)) {
                        $extensions = 'Include';
                        $line = trim($matches[1]);
                        $found[$context][$extensions][] = $line;
                    } elseif (preg_match('/^\'(.+)\'\s+=>\s+(.+)/', $line, $matches)) {
                        $extensions = $matches[1];
                        $line = trim($matches[2]);
                        $found[$context][$extensions][] = preg_replace('/^\d+\.\s+/', '', $line);
                    } else {
                        if (preg_match('/^\[(.+)\](.+)/', $line, $matches)) {
                            $label = $matches[1];
                            $line = trim($matches[2]);
                            $found[$context][$extensions][] = array($label, preg_replace('/^\d+\.\s+/', '', $line));
                        } else {
                            $found[$context][$extensions][] = preg_replace('/^\d+\.\s+/', '', $line);
                        }
                    }
                }
            }
        }
        return $found;

    }

    private static function getConfig()
    {
        $config = new Zend_Config_Ini('configs/asterisk-conf.php', APPLICATION_ENV);
        return $config->get('asterisk')->get('ami')->toArray();
    }

    /**
     * return AMI client and create if not exist
     * @return Dfi_Asterisk_Client
     */
    private static function getAmiClient()
    {
        if (!Dfi_Asterisk_Ami::$amiClient instanceof ClientImpl) {

            $config = self::getConfig();
            $c = new Zend_Config_Ini(APPLICATION_PATH . '/configs/log4php-pami.conf.php');
            $config["log4php.properties"] = $c->toArray();

            $config["log4php.properties"]['log4php']['properties']['log4php']['appender']['default']['file'] = APPLICATION_PATH . '/configs/ami.log';

            $client = new Dfi_Asterisk_Client($config);
            $client->open();

            Dfi_Asterisk_Ami::$amiClient = $client;
        }
        return Dfi_Asterisk_Ami::$amiClient;
    }
}