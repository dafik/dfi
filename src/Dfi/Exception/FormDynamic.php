<?php
namespace Dfi\Exception;

use Exception;
use Dfi\Form\Dynamic\DynamicAbstract;
use SimpleXMLElement;

class FormDynamic extends \Exception
{
    /**
     * @var SimpleXMLElement
     */
    private $xml;

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $dbg = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        foreach ($dbg as $trace) {
            if (isset($trace['object'])) {
                $obj = $trace['object'];
                if ($obj instanceof DynamicAbstract) {
                    $xml = $obj->getXML();
                    $this->xml = $xml;
                    return;
                }
            }
        }


    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getPath()
    {
        if ($this->xml) {
            $dom = dom_import_simplexml($this->xml);
            $path = $dom->getNodePath();
            return $path;
        }

        return false;
    }
}