<?
namespace Dfi\Crypt;

/**
 * Class for encoding / decoding ids
 *
 */
class Int implements McryptInterface
{

    /**
     * must be between 0..4294967295
     *
     * @var double
     */
    private static $hash = 1252386456;  //TODO get from config
    /**
     *  must be numeric and must contain 20 digits
     *
     * @var double
     */
 // private static $salt = '42378042347853842';
    private static $salt = 96833942933947843;

	/**
     * precision for decoding
     *
     * @var int
     */
    private static $precision = 6;
    /**
     * Encoding system 2..36
     *
     * @var int
     */
    private static $encoding = 28;

    /**
     * encode id
     *
     * @param int $id
     * @return string
     */
    public static function encode($id)
    {
        if (null == $id) return null;
        $counted_id = (string)($id ^ self::$hash);
        $salt_position = substr($counted_id[strlen($counted_id) - 1], 0, 1);
        $counted_id = (bcmul($counted_id, substr(self::$salt, $salt_position, 10) * 1) . $salt_position);
        return self::convert($counted_id, 10, self::$encoding);
    }

    /**
     * decode hash
     *
     * @param string $hash
     * @return string  | boolean
     */
    public static function decode($hash)
    {
        $hash = (string)preg_replace('/[^0-9a-r]/', '', $hash);
        $hash = self::convert($hash, self::$encoding, 10);
        if ($hash[strlen($hash) - 1] == null) return false;
        $salt_position = substr($hash[strlen($hash) - 1], 0, 1);
        $hash[strlen($hash) - 1] = '';
        $return_id = $hash / substr(self::$salt, $salt_position, 10) * 1;
        if (((int)pow((($return_id - round($return_id)) * 10), self::$precision)) == 0 && round($return_id) ^ self::$hash < 2147483648) {
            return round($return_id) ^ self::$hash;
        } else {
            return false;
        }
    }

    /**
     * convert between numeric systems
     *
     * @param string $numString
     * @param int $fromBase 2..36
     * @param int $toBase 2..36
     * @return string
     */
    private static function convert($numString, $fromBase, $toBase)
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $toString = substr($chars, 0, $toBase);
        $length = strlen($numString);
        $result = '';
        $number = array();

        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $numString{$i});
        }
        do {
            $divide = 0;
            $newLength = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $fromBase + $number[$i];
                if ($divide >= $toBase) {
                    $number[$newLength++] = (int)($divide / $toBase);
                    $divide = $divide % $toBase;
                } elseif ($newLength > 0) {
                    $number[$newLength++] = 0;
                }
            }
            $length = $newLength;
            $result = $toString{$divide} . $result;
        } while ($newLength != 0);
        return $result;
    }

    /**
     * @param int $encoding
     */
    public static function setEncoding($encoding)
    {
        self::$encoding = $encoding;
    }

    /**
     * @param float $hash
     */
    public static function setHash($hash)
    {
        self::$hash = $hash;
    }

    /**
     * @param int $precision
     */
    public static function setPrecision($precision)
    {
        self::$precision = $precision;
    }

    /**
     * @param float $salt
     */
    public static function setSalt($salt)
    {
        self::$salt = $salt;
    }

}
