<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 09:24
 */

namespace Dfi\Worker;


use Composer\Autoload\ClassLoader;
use Dfi\Exception\AppException;

class TaskCmd
{
    private $guid;
    private $taskClass;
    private $taskFile;
    private $args;

    public function __construct($args)
    {
        $this->guid = array_shift($args);
        $this->taskClass = array_shift($args);
        $this->taskFile = array_shift($args);

        $this->args = $args;


        $base = realpath(APPLICATION_PATH . "/../");
        $new = explode(PATH_SEPARATOR, get_include_path());
        $new [] = $base;
        set_include_path(implode(PATH_SEPARATOR, $new));

    }

    public function __destruct()
    {
        //unlink(BASE_PATH . '/data/worker/' . $this->guid);
    }

    public function run()
    {
        //TODO export to interface

        /** @var TaskInterface $object */
        $object = new $this->taskClass($this->args);

        $object->setFile($this->taskFile);
        $object->setGuid($this->guid);
        $object->setLogPath(BASE_PATH . '/data/worker');
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

defined('BASE_PATH') || define("BASE_PATH", substr(__DIR__, 0, strpos(__DIR__, "/vendor")));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(BASE_PATH . '/application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

ini_set("include_path",
    implode(PATH_SEPARATOR,
        array_merge(
            explode(PATH_SEPARATOR, ini_get("include_path")),
            [BASE_PATH, APPLICATION_PATH]
        )
    )
);


$path = BASE_PATH . '/vendor/VendorAutoloader.php';
/**
 * @var ClassLoader
 */
$loader = require_once $path;
$loader->setUseIncludePath(true);

array_shift($argv);
$task = new Task($argv);
$task->run();