<?

class  Dfi_Model_DataVersion
{


    /**
     * @param string|array|SimpleXMLElement $newData
     * @param string|array|SimpleXMLElement $currentData
     * @return bool
     * @throws Exception
     */
    private static function compareData($newData, $currentData)
    {

        $currentXml = Dfi_Xml::asSimpleXml(
            Dfi_Xml::checkIsValidXmlStirng(
                Dfi_Xml::castToXmlString($currentData)
            )
        );
        if ($currentXml instanceof SimpleXMLElement) {
            /* @var $currentBranch SimpleXMLElement */
            $currentBranch = $currentXml->current;
            $currentBranch = Dfi_Xml::checkIsValidXmlStirng($currentBranch->asXML(), true, true);

            $newBranch = Dfi_Xml::asSimpleXml(Dfi_Xml::checkIsValidXmlStirng(Dfi_Xml::castToXmlString($newData), true, true));
            $tmp = new SimpleXMLElement('<current></current>');
            foreach ($newBranch as $name => $value) {
                $tmp->addChild($name, $value);
            }
            $newBranch = Dfi_Xml::checkIsValidXmlStirng($tmp->asXML(), true, true);

            if ($newBranch == $currentBranch) {
                return false;
            }
        }
        return true;
    }

    public static function setData($newData, $currentData)
    {
        if (!$newData) {
            return false;
        }

        if (self::compareData($newData, $currentData)) {

            $currentXml = Dfi_Xml::asSimpleXml(Dfi_Xml::castToXmlString($currentData));

            $dom = self::makeDomFromNewData($newData);
            $x = Dfi_Xml::asSimpleXml($dom->saveXML());

            self::applyOldVersions($dom, $currentXml);
            $x = Dfi_Xml::asSimpleXml($dom->saveXML());

            $histories = self::applyCurrentHistory($dom);
            $x = Dfi_Xml::asSimpleXml($dom->saveXML());

            self::applyOldHistories($dom, $histories, $currentXml);
            $x = Dfi_Xml::asSimpleXml($dom->saveXML());


            $newData = $dom->saveXML($dom);
        } else {
            $newData = Dfi_Xml::castToXmlString($currentData);
        }
        return Dfi_Xml::checkIsValidXmlStirng($newData);
    }

    /**
     * @param $newData
     * @return DOMDocument
     * @throws Exception
     */
    private static function makeDomFromNewData($newData)
    {
        $newXmlString = Dfi_Xml::castToXmlString($newData);
        $newXml = Dfi_Xml::asSimpleXml($newXmlString);

        $x = Dfi_Xml::asSimpleXml('<current></current>');
        /** @var SimpleXMLElement $value */
        foreach ($newXml as $key => $value) {
            if ((string)$value != '') {
                $x->addChild($key, $value);
            }
        }
        $newXml = $x;

        //new DOM document definition
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = FALSE;
        $dom->encoding = 'UTF-8';


        $newDataDom = dom_import_simplexml($newXml);

        $currentDom = $dom->createElement('data');
        $dom->appendChild($currentDom);

        $newDataImported = $dom->importNode($newDataDom, true);
        $currentDom->appendChild($newDataImported);


        $x = $dom->saveXML($dom);

        return $dom;


    }


    /**
     * @param DOMDocument $dom
     * @return DOMElement
     * @internal param $histories
     */
    private static function applyCurrentHistory(DOMDocument $dom)
    {

        //create and add histories container
        $histories = $dom->createElement('histories');
        $dom->firstChild->appendChild($histories);

        //add new history entry as last child of histories
        $history = $dom->createElement('history');
        $histories->appendChild($history);

        //add userid and date to new history entry
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($user instanceof SysUser) {
            $userId = $dom->createElement('user', $user->getId());
        }
        $now = new DateTime();
        $date = $dom->createElement('date', $now->format('Y-m-d H:i:s'));

        $history->appendChild($userId);
        $history->appendChild($date);


        return $histories;
    }

    /**
     * @param $dom
     * @param $histories
     * @param $currentXml
     * @internal param $currentHistoriesDom
     */
    private static function applyOldHistories($dom, $histories, $currentXml)
    {
        /* $currentHistories SimpleXMLElement */
        $currentHistories = $currentXml->histories;
        if ((string)$currentHistories == '') {
            return;
        }
        $currentHistoriesDom = dom_import_simplexml($currentHistories);

        //import old histories
        if ($currentHistoriesDom) {
            // if histories import old values to new container;
            $historiesImport = $dom->importNode($currentHistoriesDom, true)->childNodes;
            while ($historiesImport->length > 0) {
                $history = $historiesImport->item(0);
                $histories->appendChild($history);
            }
        }
    }

    /**
     * @param $dom
     * @param $currentXml
     * @internal param $currentVersionsDom
     * @internal param $versions
     * @internal param $currentDom
     */
    private static function applyOldVersions(DOMDocument $dom, $currentXml)
    {
        $currentBranch = $currentXml->current;

        if ((string)$currentBranch == '') {
            return;
        }

        $currentDom = dom_import_simplexml($currentBranch);


        //create and add versions container
        $versions = $dom->createElement('versions');
        $dom->firstChild->appendChild($versions);

        //current add old current as last child of versions
        $version = $dom->createElement('version');
        $versions->appendChild($version);

        //import values of old current for new version
        $childImport = $dom->importNode($currentDom, true)->childNodes;
        while ($childImport->length > 0) {
            $child = $childImport->item(0);
            $version->appendChild($child);
        }


        $currentVersions = $currentXml->versions;
        if ((string)$currentVersions == '') {
            return;
        }


        //import old versions
        $currentVersionsDom = dom_import_simplexml($currentVersions);
        if ($currentVersionsDom) {
            // if version import old values to new container;
            $versionsImport = $dom->importNode($currentVersionsDom, true)->childNodes;
            while ($versionsImport->length > 0) {
                $version = $versionsImport->item(0);
                $versions->appendChild($version);
            }
        }


    }
}