<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 09:24
 */

namespace Dfi\Worker;


use Dtk\Importer\Base;

class Task
{
    public function __construct($arg)
    {
        $this->guid = $arg[0];
        $this->importerClass = $arg[1];
        $this->importFile = $arg[2];
    }

    public function __destruct()
    {
        //unlink(BASE_PATH . '/data/worker/' . $this->guid);
    }

    public function run()
    {
        /** @var Base $importer */
        $importer = new $this->importerClass();
        $importer->setFile($this->importFile);
        $importer->setGuid($this->guid);
        $importer->setLogPath(BASE_PATH . 'data/worker');
        $importer->setMakeLog(true);

        $this->makeLog();


        $importer->import();
    }

    private function makeLog()
    {
        $path = BASE_PATH . '/data/worker/' . $this->guid;

        $data = new \stdClass();
        $data->file = $this->importerClass;
        $data->guid = $this->guid;
        $data->args = [$this->importFile];
        $data->pid = getmypid();
        $data->progress = 0;

        file_put_contents($path, json_encode($data));
    }

}

defined('BASE_PATH') || define("BASE_PATH", substr(__DIR__, 0, strpos(__DIR__, "vendor")));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(BASE_PATH . 'application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

ini_set("include_path",
    implode(PATH_SEPARATOR,
        array_merge(
            explode(PATH_SEPARATOR, ini_get("include_path")),
            [BASE_PATH, APPLICATION_PATH]
        )
    )
);


$path = BASE_PATH . 'vendor/VendorAutoloader.php';
require_once $path;

array_shift($argv);
$task = new Task($argv);
$task->run();