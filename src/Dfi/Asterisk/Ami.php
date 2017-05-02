<?

namespace Dfi\Asterisk;

use Dfi\App\Config;
use Dfi\Controller\Action\Helper\Messages;
use Dfi\Iface\Provider\Pbx\AstConfigProvider;
use Exception;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\CommandAction;
use PAMI\Message\OutgoingMessage;
use PAMI\Message\Response\ResponseMessage;
use Zend_Config_Ini;


class Ami
{
    /**
     * AMI Client object
     * @var Client
     */
    private static $amiClient;

    public static function reload()
    {
        try {
            $client = self::getAmiClient();
            if (!Config::getString('asterisk.fake', true)) {
                $res = $client->send(new CommandAction('core reload'));
            } else {
                $res = 'fake';
            }
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
            $res = $e;
        }
        return $res;
    }

    public static function reloadDialplan()
    {
        try {
            $client = self::getAmiClient();
            if (!Config::getString('asterisk.fake', true)) {
                $res = $client->send(new CommandAction('dialplan reload'));
            } else {
                $res = 'fake';
            }
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
            $res = $e;
        }
        return $res;
    }

    public static function reloadSip()
    {
        try {
            $client = self::getAmiClient();
            if (!Config::getString('asterisk.fake', true)) {
                $res = $client->send(new CommandAction('sip reload'));
            } else {
                $res = 'fake';
            }
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
            $res = $e;
        }
        return $res;
    }

    public static function reloadQueues()
    {
        try {
            $client = self::getAmiClient();
            if (!Config::getString('asterisk.fake', true)) {
                $res = $client->send(new CommandAction('queue reload all'));
            } else {
                $res = 'fake';
            }
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
            $res = $e;
        }
        return $res;
    }

    /**
     * @param OutgoingMessage $message
     * @return ResponseMessage
     */
    public static function send(OutgoingMessage $message)
    {
        try {
            $client = self::getAmiClient();
            if (!Config::get('asterisk.fake')) {
                $res = $client->send($message);
                //sleep(5);
                return $res;
            }
            return true;
        } catch (Exception $e) {
            Messages::getInstance()->addMessage('debug', $e->getMessage());
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

        $res = Ami::send(new \PAMI\Message\Action\CommandAction('dialplan show ' . $name));

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


    public static function getConfigMappings()
    {
        $found = [];

        $res = Ami::send(new \PAMI\Message\Action\CommandAction('core show config mappings'));
        if (!$res instanceof \PAMI\Message\Response\ResponseMessage) {

            $providerClass = \Dfi\Iface\Helper::getClass('iface.provider.pbx.astConfig');
            /** @var AstConfigProvider $provider */
            $provider = $providerClass::create();
            $cat = $provider
                ->select('FileName')
                ->distinct()
                ->find();

            return $cat->toArray();
        }
        $lines = explode("\n", array_pop(explode("\r\n", $res->getRawContent())));


        $i = 0;

        $started = false;
        while (true) {
            $line = $lines[$i];

            if ($started && preg_match('/Config Engine/', $line)) {
                break;
            }
            if ($started) {
                $found[] = preg_replace('/\s*===> ([a-z]+\.conf).*/', '$1', $line);
            }
            if ($line == 'Config Engine: odbc') {
                $started = true;
            }
            $i++;
            if ($i == count($lines)) {
                break;
            }
        }
        return $found;
    }


    private static function getConfig()
    {
        $config = new Zend_Config_Ini('configs/ini/asterisk.ini', APPLICATION_ENV);
        return $config->get('ami')->toArray();
    }

    /**
     * return AMI client and create if not exist
     * @return Client
     */
    private static function getAmiClient()
    {
        if (!Ami::$amiClient instanceof ClientImpl) {

            $config = self::getConfig();
            $c = new Zend_Config_Ini(APPLICATION_PATH . '/configs/sys/log4php-pami.conf.php');
            $config["log4php.properties"] = $c->toArray();

            $config["log4php.properties"]['appenders']['appender']['default']['file'] = Config::get('paths.log') . 'ami.log';

            $client = new Client($config);
            if (!Config::getString('asterisk.fake', true)) {
                $client->open();
            }

            Ami::$amiClient = $client;
        }
        return Ami::$amiClient;
    }
}