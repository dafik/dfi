<?php

namespace Dfi\Asterisk;

use Criteria;
use DateTime;
use Dfi\App\Config;
use Dfi\Iface\Model\Asterisk\Billing;
use Dfi\Iface\Provider\Asterisk\BillingProvider;
use Exception;

class Recordings
{
    /**
     * Enter description here ...
     * @var resource
     */
    private static $ssh;
    private static $localPath;
    private static $remotePath;

    /**
     * @return string
     */
    public static function getLocalPath()
    {
        if (!self::$localPath) {
            self::$localPath = Config::get("services.recordings.localPath", "");
        }
        return self::$localPath;
    }

    /**
     * @return string
     */
    public static function getRemotePath()
    {
        if (!self::$remotePath) {
            self::$remotePath = Config::get("services.recordings.remotePath", "");
        }
        return self::$remotePath;
    }


    public static function convertFormatByDate(DateTime $from, DateTime $to)
    {
        $diff = $to->diff($from);
        $days = $diff->format('%d');

        while ($days > 0) {
            $path = self::getLocalPath() . '/' . $to->format('Y') . '/' . $to->format('m') . '/' . $to->format('d');

            if (file_exists($path)) {
                $command = 'cd ' . $path . ';ls *.gsm 2>/dev/null';
                $out1 = array();
                $res1 = exec($command, $out1, $var);

                foreach ($out1 as $gsmFile) {
                    self::convertFileGsm2Wav($path . '/' . $gsmFile, true);
                }
            }
            $days--;
            $to->modify('-1 day');
        }
    }

    public static function convertFileGsm2Wav($pathName, $delete = false)
    {
        if (file_exists($pathName)) {

            $command = 'sox ' . $pathName . ' -s ' . str_replace('.gsm', '.wav', $pathName);
            $out = array();
            $x = exec($command . ' 2>&1', $out, $res);

            if ($res > 0) {
                return false;
            }

            if ($delete) {
                $command = 'rm ' . $pathName;
                $x = exec($command . ' 2>&1', $out, $res);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function convertFileGsm2Ogg($pathName, $delete = false)
    {
        if (file_exists($pathName)) {

            $command = 'sox ' . $pathName . ' -s ' . str_replace('.gsm', '.ogg', $pathName);
            $out = array();
            $x = exec($command . ' 2>&1', $out, $res);

            if ($delete) {
                $command = 'rm ' . $pathName;
                $x = exec($command . ' 2>&1', $out, $res);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * find and copy files to ftps folder based on date range and sip range
     * @param DateTime $from
     * @param DateTime $to
     * @param array $sips
     * @throws \PropelException
     */
    public static function recordings(DateTime $from, DateTime $to, $sips = array())
    {

        if (count($sips) == 0) {
            $sips = range('920', '930', 1);
        }


        $class = \Dfi\Iface\Helper::getClass("iface.provider.asterisk.billing");

        /** @var BillingProvider $connections */
        $connections = new $class();
        $connections
            ->filterByCalldate($from->format('Y-m-d H:i:s'), Criteria::GREATER_EQUAL)
            ->filterByCalldate($to->format('Y-m-d H:i:s'), Criteria::LESS_EQUAL)
            ->condition('cond1', 'Billing.Src in (' . implode(',', $sips) . ')');

        $i = 0;
        $conditions = array();
        foreach ($sips as $sip) {
            $name = 'cond_' . $i;
            $conditions[] = $name;
            $i++;
            $connections->condition($name, 'Billing.Dstchannel like \'SIP/' . $sip . '%\'');
        }
        $connections->combine($conditions, 'or', 'cond2')
            ->where(array('cond1', 'cond2'), 'or');

        $bills = $connections->find()->getArrayCopy();

        self::copyByBills($bills);

    }

    /**
     * copy files to ftps folder based on billing objects array
     * @param  Billing[] $bills
     */
    public static function copyByBills($bills)
    {
        $files = array();
        $notfound = array();

        foreach ($bills as $billing) {
            /* @var $billing Billing */

            if (($billing->getCcRecfilename() && $billing->getUserfield()) || (self::findRecording($billing) && self::findData($billing))) {
                $path = explode('/', $billing->getCcRecfilename());
                $name = array_pop($path);
                $files[] = array(
                    'src' => self::getRemotePath() . $billing->getCcRecfilename() . '.gsm',
                    'dst' => self::getLocalPath() . self::convertName($billing) . '.gsm',
                    'billing' => $billing
                );
            } else {
                if ($billing->getBillsec() > 0) {
                    $notfound[] = $billing;
                }
            }
        }
        $notCopied = self::copyssh($files);
    }

    /**
     * Returns ssh resource
     * @return resource
     * @throws Exception
     */
    private static function getSsh()
    {
        if (!self::$ssh) {
            $conf = Config::get('services.recordings.ssh');
            $connection = ssh2_connect($conf->get('host'), $conf->get('port'));
            $res = ssh2_auth_password($connection, $conf->get('user'), $conf->get('password'));
            if ($res) {
                self::$ssh = $connection;
            } else {
                throw new Exception('cant connect by ssh');
            }
        }
        return self::$ssh;
    }

    /**
     * Find and set inn billig audio file
     * @param Billing $billing
     * @param boolean $forceSearch
     * @return boolean
     */
    public static function findRecording($billing, $forceSearch = false)
    {

        if ($billing->getCcRecfilename() && !$forceSearch) {
            return true;
        }

        $connection = self::getSsh();

        //mnt/nagrania/current/mob/2012/06/05/
        $path = self::getRemotePath() . 'mob/' . $billing->getCalldate('Y') . '/' . $billing->getCalldate('m') . '/' . $billing->getCalldate('d');
        $uniqe = $billing->getUniqueid();

        $command = 'cd ' . $path . ' && printf "%s\n" *' . $uniqe . '*;';
        //$command = 'als '.$path.'/*'.$uniqe.'*';

        $stream = ssh2_exec($connection, $command);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);

        $output = stream_get_contents($stream);
        $output1 = stream_get_contents($errorStream);

        // Close the streams
        fclose($errorStream);
        fclose($stream);


        $x = $output != '*' . $uniqe . '*' . "\n";


        if (!$output1 && $output != '*' . $uniqe . '*' . "\n") {
            $output = trim($output);
            $output = str_replace('.gsm', '', $output);

            $filename = 'mob/' . $billing->getCalldate('Y') . '/' . $billing->getCalldate('m') . '/' . $billing->getCalldate('d') . '/' . $output;
            $billing->setCcRecfilename($filename);
            $billing->save();

            return true;
        } else {
            $billing->setCcRecfilename('false');
            $billing->save();
        }

        return false;
    }

    private static function findData()
    {
        return true;
    }

    public static function convertName(Billing $billing)
    {
        $path = explode('/', $billing->getCcRecfilename());
        $name = array_pop($path);

        if (strlen($billing->getSrc()) == 3) {
            $sip = $billing->getSrc();
        } else {
            $sip = '';
            $matches = array();
            if (preg_match('/^SIP\/(\d+)-/', $billing->getDstchannel(), $matches)) {
                $sip = $matches[1];
            }
        }

        $datematches = array();
        $namedate = preg_match('/\d{8}-\d{6}/', $name, $datematches);

        $newName = $billing->getPrimaryKey() . '-' . $datematches[0] . '-' . $sip;

        /*$mob = array_search('mob', $path);
        unset($path[$mob]);*/

        $path[] = $newName;

        return implode('/', $path);

    }

    private static function copyssh($files)
    {
        $connection = self::getSsh();

        $notfoud = array();

        foreach ($files as $file) {
            $name = $file['dst'];
            $newName = str_replace('.gsm', '.wav', $name);
            if (!file_exists($file['dst']) && !file_exists($newName)) {
                $path = explode('/', $file['dst']);
                $filname = array_pop($path);
                $dir = implode('/', $path);
                if (!file_exists($dir)) {
                    $res = mkdir($dir, 0777, true);
                }
                $res2 = ssh2_scp_recv($connection, $file['src'], $file['dst']);
                if (!$res2) {
                    $billing = $file['billing'];
                    self::findRecording($billing, true);

                    $pathname = self::getLocalPath() . '/' . $filname;

                    $name = str_replace('.gsm', '.txt', $pathname);
                    $r = file_put_contents($name, 'brak');
                    $notfoud[] = $file;
                }
            }
        }
        return $notfoud;
    }
}