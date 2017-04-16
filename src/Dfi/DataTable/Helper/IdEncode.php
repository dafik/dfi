<?

namespace Dfi\DataTable\Helper;


use Dfi\Crypt\Int;

class IdEncode
{
    /**
     * @param $id
     * @return false|string
     */
    public static function run($id)
    {
        return Int::encode($id);
    }
}