<?
namespace Dfi\Auth;

use Dfi\App\Config;
use Zend_Controller_Request_Http;

class Bypass
{
    const WILDCARD_CHAR = '*';

    public static function isBypass(Zend_Controller_Request_Http $request)
    {

        $path = [
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        ];

        $config = Config::get('bypass', [], true);

        return self::isBypassSet($path, $config);
    }

    private static function isBypassSet($path, $config)
    {
        $identifier = array_shift($path);


        if (isset ($config[$identifier])) {
            $config = $config[$identifier];
            if ($config == self::WILDCARD_CHAR) {
                return true;
            } elseif (is_array($config)) {
                return self::isBypassSet($path, $config);
            }
        }
        return false;
    }
}