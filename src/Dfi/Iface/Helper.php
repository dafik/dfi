<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:43
 */

namespace Dfi\Iface;


use Dfi\App\Config;

class Helper
{
    public static function getClass($name)
    {
        return Config::get($name);
    }

    public static function getObject($name)
    {
        $name = Config::get($name);

        return new $name();
    }


}