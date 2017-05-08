<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 06.05.17
 * Time: 15:51
 */

namespace Dfi\View\Helper;


class Id
{
    public static function make()
    {
        return md5(date("Y-m-dTH:i:s") . substr((string)microtime(), 1, 8));
    }
}