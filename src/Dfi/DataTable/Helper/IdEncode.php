<?

namespace Dfi\DataTable\Helper;


use Dfi\Crypt\Integer;

class IdEncode
{
    /**
     * @param $id
     * @return false|string
     */
    public static function run($id)
    {
        return Integer::encode($id);
    }
}