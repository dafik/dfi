<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 09:24
 */

namespace Dfi\Worker;


use Dfi\Exception\AppException;

class Task
{
    private $guid;
    private $taskClass;
    private $taskFile;

    public function __construct($arg)
    {
        $this->guid = $arg[0];
        $this->taskClass = $arg[1];
        $this->taskFile = $arg[2];
    }

    public function __destruct()
    {
        //unlink(BASE_PATH . '/data/worker/' . $this->guid);
    }

    public function run()
    {
        //TODO export to interface

        /** @var TaskInterface $object */
        $object = new $this->taskClass();
        $object->setFile($this->taskFile);
        $object->setGuid($this->guid);
        $object->setLogPath(BASE_PATH . 'data/worker');
        $object->setMakeLog(true);

        $this->makeLog();

        try {

            $object->run();
        } catch (AppException $e) {
            $path = BASE_PATH . '/data/worker/' . $this->guid;
            $current = json_decode(file_get_contents($path));

            $current->error = $e->getMessage();
            file_put_contents($path, json_encode($current));

        }
    }

    private function makeLog()
    {
        $path = BASE_PATH . '/data/worker/' . $this->guid;

        $data = new \stdClass();
        $data->file = $this->taskClass;
        $data->guid = $this->guid;
        $data->args = [$this->taskFile];
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