<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 08:59
 */

namespace Dfi;


class Worker
{
    private $file;
    private $args;
    private $guid;
    private $interpreter;

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }


    public function __construct($file, $args)
    {
        $this->guid = $this->makeGUID();
        $this->args = $args;
        if (is_array($file)) {
            $this->file = $file[0];
            $this->interpreter = $file[1];
        } else {
            $this->file = $file;
        }
    }


    private function makeGUID()
    {
        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);

        return $uuid;
    }


    public function run($runInBackground = true)
    {

        if ($this->interpreter) {

            $logFile = realpath(APPLICATION_PATH . "/../data/worker") . '/' . $this->guid . '.log';

            exec("which " . $this->interpreter, $output, $returnVar);
            if ($returnVar != 0) {
                return;
            }
            $interpreter = array_shift($output);
            $filePath = realpath(APPLICATION_PATH . '/../' . $this->file);
            if (!file_exists($filePath)) {
                return;
            }

            $command = $interpreter . ' ' . $filePath . ' "' . $this->guid . '" "' . implode("\" \"", $this->args) . "\"";

            $fullCommand = 'nohup ' . $command . ' > ' . $logFile . ' 2>&1 & echo $!';
            $pid = exec($fullCommand);

            if ($pid) {
                return true;
            }
            return false;
        }
        $res = false;

        if ($runInBackground) {


            $logFile = realpath(APPLICATION_PATH . "/../data/worker") . '/' . $this->guid . '.log';
            $errorLog = realpath(APPLICATION_PATH . "/../data/worker") . '/' . $this->guid . '.error.log';

            $phpOptions = [
                '-n',
                '-d memory_limit=-1',
                '-dextension=mysqlnd.so',
                '-dextension=pdo.so',
                '-dextension=pdo_mysql.so',
                '-dextension=json.so',
                '-dextension=dom.so',
                '-dextension=xml.so',
                '-dextension=simplexml.so',
                '-dextension=xmlwriter.so',
                '-dextension=iconv.so',
                '-dextension=zip.so',
                '-dextension=tidy.so',
                '-dextension=ctype.so',
                '-dlog_errors=On',
                '-derror_reporting=E_ALL',
                '-derror_log=' . $errorLog,

                '-f'
            ];
            $command = "/usr/bin/php " . implode(" ", $phpOptions) . realpath(APPLICATION_PATH . "/../vendor/dafik/dfi/src/Dfi/Worker/Task.php") . ' "' . $this->guid . '" "' . $this->file . '" "' . implode("\" \"", $this->args) . "\"";
            $fullCommand = 'nohup ' . $command . ' > ' . $logFile . ' 2>&1 & echo $!';
            $pid = exec($fullCommand);

            if ($pid) {
                return true;
            }


        } else {

            $dtkPath = realpath(APPLICATION_PATH . "/../vendor/dafik/dtk/src/");

            $class = new $this->file($this->args);
            $class->setFile($this->args[0]);

            $res = $class->run($this->args);


        }
        return $res;
    }
}