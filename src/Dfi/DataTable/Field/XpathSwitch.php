<?php
namespace Dfi\DataTable\Field;

class XpathSwitch extends Xpath
{

    protected $xpath;
    protected $id;
    protected $testValue;
    protected $returnTrue;
    protected $returnFalse;

    public function __construct($key, $xpath, $testValue, $returnTrue, $returnFalse)
    {
        parent::__construct($key, $xpath);


        $this->testValue = $testValue;
        $this->returnTrue = $returnTrue;
        $this->returnFalse = $returnFalse;
    }

    public static function create($key, $xpath, $testValue, $returnTrue, $returnFalse)
    {
        return new XpathSwitch($key, $xpath, $testValue, $returnTrue, $returnFalse);
    }

    protected function getExpression($query)
    {
        return 'ExtractValue(' . $this->getRealcolumnName($query) . ', \'' . $this->getXpath() . '\')';
    }

    protected function getXpath()
    {
        /*
        concat(
            substring("TAK",1,(((//process[result_id=402]/result_id[last()]) = 402)*3)),
            substring("NIE",1,((string-length(//process[result_id=402]/result_id[last()])=0)*3))
        )
        zar. na weryf. pesel|!(Xml::getDataByXpath($collected->getProcessDatum()->getData(), '//process[result_id=200]/result_id[last()]') == 200 ? 'TAK' : 'NIE')
        z anulacji OPl|!(Xml::getDataByXpath($collected->getProcessDatum()->getData(), '//process[result_id=242]/result_id[last()]') == 242 ? 'TAK' : 'NIE')

        */
        $xpath = $this->xpath;

        if (false !== strpos($xpath, 'last()')) {
            if (substr($xpath, 0, 1) == '(') {
                $xpath = substr($xpath, 1);
                $pos = strpos($xpath, ')');
                $xpath = substr($xpath, 0, $pos) . substr($xpath, $pos + 1);
            }
            $xpath = str_replace('//', '/descendant-or-self::', $xpath);
        }


        $condition = 'concat(substring("' . $this->returnTrue . '",1,(((' . $xpath . ') = ' . $this->testValue . ')*' . strlen($this->returnTrue) . ')), substring("' . $this->returnFalse . '",1,((string-length(' . $xpath . ')=0)*' . strlen($this->returnTrue) . ')))';


        return $condition;
    }
}