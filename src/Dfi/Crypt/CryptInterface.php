<?php

namespace Dfi\Crypt;
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 09.07.14
 * Time: 10:15
 */

/**
 * Class for encoding / decoding ids
 *
 */
interface CryptInterface
{
    /**
     * decode hash
     *
     * @param string $hash
     * @return string  | boolean
     */
    public static function decode($hash);

    /**
     * encode string
     *
     * @param int $id
     * @return string
     */
    public static function encode($id);
}