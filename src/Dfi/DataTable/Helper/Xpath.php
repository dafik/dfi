<?

namespace Dfi\DataTable\Helper;

use Dfi\Xml;

class Xpath
{

    public static function run($xml, $options)
    {
        $xpath = $options['xpath'];
        return (string)Xml::getDataByXpath($xml, $xpath);
    }
}