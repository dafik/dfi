<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 20.07.17
 * Time: 08:46
 */

namespace Dfi;


class ArrayUtils
{
    public static function deepDiff($arr1, $arr2)
    {
        $diff = array_diff(array_map('json_encode', $arr1), array_map('json_encode', $arr2));
        return array_map('json_decode', $diff);
    }

    public static function flatten($array)
    {
        if (!is_array($array)) {
            return $array;
        } else {
            return json_encode($array);
        }
    }
}