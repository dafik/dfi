<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 09:24
 */

namespace Dfi\Worker;

interface TaskInterface
{
    public function setFile($importFile);

    public function setGuid($guid);

    public function setLogPath($string);

    public function setMakeLog($true);

    public function run();
}