<?php

class Dfi_Xml
{


    /**
     * Build A XML Data Set
     *
     * @param array $data Associative Array containing values to be parsed into an XML Data Set(s)
     * @param string $startElement Root Opening Tag, default fx_request
     * @param string $xml_version XML Version, default 1.0
     * @param string $xml_encoding XML Encoding, default UTF-8
     * @return string XML String containig values
     * @return mixed Boolean false on failure, string XML result on success
     */
    public static function from_array($data, $startElement = 'root', $xml_version = '1.0', $xml_encoding = 'UTF-8')
    {

        if (!is_array($data)) {
            $err = 'Invalid variable type supplied, expected array not found on line ' . __LINE__ . " in Class: " . __CLASS__ . " Method: " . __METHOD__;
            trigger_error($err);
            return false; //return false error occurred
        }
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument($xml_version, $xml_encoding);
        $xml->startElement($startElement);

        self::write($xml, $data);

        $xml->endElement(); //write end element
        //returns the XML results
        return $xml->outputMemory(true);
    }


    /**
     * @param XMLWriter $xml
     * @param $data
     */
    public static function write(XMLWriter $xml, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                foreach ($value as $itemValue) {
                    //$xml->writeElement($key, $itemValue);

                    if (is_array($itemValue)) {
                        $xml->startElement($key);
                        self::write($xml, $itemValue);
                        $xml->endElement();
                        continue;
                    }

                    if (!is_array($itemValue)) {
                        $xml->writeElement($key, $itemValue . "");
                    }
                }
            } else if (is_array($value)) {
                $xml->startElement($key);
                self::write($xml, $value);
                $xml->endElement();
                continue;
            }

            if (!is_array($value)) {
                $xml->writeElement($key, $value . "");
            }
        }
    }


    /**
     * @param $stringXml
     * @param bool $emptyToNull remove empty arrays with null
     * @return array
     */
    public static function asArray($stringXml, $emptyToNull = false)
    {
        if (is_string($stringXml)) {
            $xml = simplexml_load_string($stringXml);
        } else {
            $xml = $stringXml;
        }
        if ($xml) {
            $json = json_encode((array)$xml);
            $decoded = json_decode($json, TRUE);
            if ($emptyToNull) {
                array_walk($decoded, array('self', 'removeEmpty'));
            }
            return $decoded;

        }
        return array();
    }

    private static function removeEmpty(&$val, $key)
    {
        if (is_array($val)) {

            if (count($val) == 0) {
                $val = null;
            } else {
                array_walk($val, array('self', 'removeEmpty'));
            }
        }
    }

    /**
     * @param $stringXml
     * @return array|mixed
     */
    public static function to_array($stringXml)
    {
        return self::asArray($stringXml);
    }

    /**
     * @static
     * @param $stringXml
     * @return SimpleXMLElement
     * @throws Exception
     */
    public static function asSimpleXml($stringXml)
    {
        if (!$stringXml) {
            $stringXml = new SimpleXMLElement('<dupa></dupa>');
            $stringXml = $stringXml->asXML();
            //throw new Exception('string cant be empty');
        }
        $sxe = simplexml_load_string($stringXml);
        if ($sxe instanceof SimpleXMLElement) {
            return $sxe;
        } else {
            self::format_xml_errors($stringXml);
            return false;
        }
    }

    /**
     * @param $stringXml
     * @return string
     */
    public static function normalizeXml($stringXml)
    {
        $data = str_replace('<?xml version="1.0"?>', '', $stringXml);
        $data = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $data);
        $data = preg_replace('/<(.+)\/>/', '$1', $data);
        $data = preg_replace('/<\/(.+)>/', '', $data);
        $data = preg_replace('/<(.+)>/', '$1:', $data);
        return $data;
    }


    /**
     * @param $xmlstr
     * @return string
     */
    public static function format_xml_errors($xmlstr)
    {
        $errors = libxml_get_errors();
        $out = array();

        if ($errors) {

            foreach ($errors as $xmlError) {
                $out[] = self::display_xml_error($xmlError, $xmlstr);
            }
        }
        return implode("\n", $out);
    }


    /**
     * @param libXMLError $error
     * @param $xmlstr
     * @return string
     */
    public static function display_xml_error(LibXMLError $error, $xmlstr)
    {
        $xml = explode("\n", $xmlstr);

        $return = $xml[$error->line - 1] . "\n";
        $return .= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $return .= "\n  File: $error->file";
        }

        return "$return\n\n--------------------------------------------\n\n";
    }

    /**
     * @param $value
     * @param bool $pretty
     * @param bool $removeEmpty
     * @return string
     * @throws Exception
     */
    public static function checkIsValidXmlStirng($value, $pretty = true, $removeEmpty = false)
    {
        if (!$value) {
            return false;
        }
        $v = simplexml_load_string($value);
        if (false === $v) {
            throw new Exception('malformed xml');
        }
        $v = $v->asXML();

        if ($pretty || $removeEmpty) {
            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = FALSE;
            $dom->loadXML($value);
            $xpath = new DOMXPath($dom);
            $dom->formatOutput = TRUE;
            $dom->encoding = 'UTF-8';

            if ($pretty) {

                foreach ($xpath->query('//text()') as $domText) {
                    $domText->data = trim($domText->nodeValue);
                }
            }
            if ($removeEmpty) {
                foreach ($xpath->query('//*[not(node())]') as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
            $v = $dom->saveXML();
        }
        return $v;
    }

    /**
     * @param $xml
     * @param $xpathQuery
     * @param bool $singleAsArray
     * @param null $default
     * @return bool|SimpleXMLElement|SimpleXMLElement[]
     */
    public static function getDataByXpath($xml, $xpathQuery, $singleAsArray = false, $default = null)
    {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = self::asSimpleXml($xml);
        }
        if ($xml) {
            $elements = $xml->xpath($xpathQuery);

            if ($elements && count($elements) > 0) {
                if (count($elements) > 1) {
                    return $elements;
                } elseif ($singleAsArray) {
                    return $elements;
                } else {
                    return $elements[0];
                }
            }
        }
        if ($default !== null) {
            return $default;
        }
        return false;
    }

    /**
     * @param $xml
     * @param $xpathQuery
     * @return int
     */
    public static function countDataByXpath($xml, $xpathQuery)
    {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = self::asSimpleXml($xml);
        }
        if ($xml) {
            $elements = $xml->xpath($xpathQuery);
            if ($elements) {
                return count($elements);
            }
        }

        return 0;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $xpathQuery
     * @param $value
     * @param bool $returnAsString
     * @return object|SimpleXMLElement|string
     * @throws Exception
     */
    public function setDataByXpath(SimpleXMLElement $xml, $xpathQuery, $value, $returnAsString = false)
    {
        if ($value) {
            if (!$xml) {
                $xml = simplexml_load_string(Dfi_Xml::from_array(array()));
            }
            $elements = $xml->xpath($xpathQuery);

            if ($elements && count($elements) > 0) {
                if (count($elements) > 1) {
                    throw new Exception('multiple elements found dont know which update');
                } else {
                    $element = $elements[0];
                    /* @var $element SimpleXMLElement */
                    $val = (string)$element;
                    if ($value != $val) {
                        dom_import_simplexml($element)->nodeValue = $value;
                    }
                }
            } else {
                $parts = explode('/', $xpathQuery);
                $last = array_pop($parts);
                $xml->$last = $value;
            }
            if ($returnAsString) {
                return self::checkIsValidXmlStirng($xml->asXML());
            }
        }
        return $xml;
    }


    /**
     * @param $array
     * @return array
     */
    public static function flatten($array)
    {
        if (is_string($array)) {
            $array = self::asSimpleXml($array);
        }
        if ($array instanceof SimpleXMLElement) {
            $array = self::asArray($array);
        }

        $return = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::walk($value, $key));
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * @param $array
     * @param $parent
     * @return array
     */
    private static function walk($array, $parent)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::walk($value, $key));
            } else {
                $return[$parent . '.' . $key] = $value;
            }
        }
        return $return;
    }

    /**
     * @param $stringXml
     * @return mixed
     */
    public static function removeEmptyChildren($stringXml)
    {
        if (is_string($stringXml)) {
            $stringXml = self::asSimpleXml($stringXml);
        }

        $empty = $stringXml->xpath('//*[not(text())]');
        foreach ($empty as $node) {
            $dom = dom_import_simplexml($node);
            $dom->parentNode->removeChild($dom);

        }

        $xml = $stringXml->asXML();

        return $xml;
    }

    /**
     * @param $stringXml
     * @return mixed
     */
    public static function removeUidAttribute($stringXml)
    {
        if (is_string($stringXml)) {
            $stringXml = self::asSimpleXml($stringXml);
        }

        $empty = $stringXml->xpath('//*[@uid]');
        foreach ($empty as $node) {
            unset($node[0]['uid']);


        }

        $xml = $stringXml->asXML();

        return $xml;
    }

    /**
     * @param SimpleXMLElement $node
     * @param $cdata_text
     */
    public static function SXEaddCData(SimpleXMLElement $node, $cdata_text)
    {
        $node = dom_import_simplexml($node);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }


    /**
     * @param SimpleXMLElement $xml
     * @param $nodeName
     * @param null $nodeValue
     * @param bool $position
     * @param bool $returnPosition
     * @return bool|int|string
     */
    public static function hasChild(SimpleXMLElement $xml, $nodeName, $nodeValue = null, $position = false, $returnPosition = false)
    {
        if (isset($xml->$nodeName)) {
            if ($nodeValue === null) {
                return true;
            }
            if (count($xml->$nodeName) > 1) {
                foreach ($xml->$nodeName as $key => $tmp) {
                    $elemValue = (string)$tmp;
                    if ($elemValue == $nodeValue) {
                        if ($position !== false) {
                            return $key == $position;
                        }

                        if ($returnPosition) {
                            return $key;
                        }
                        return true;
                    }
                }
            } else {
                $elemValue = (string)$xml->$nodeName;
                if ($elemValue == $nodeValue) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $nodeName
     * @param int $direction 0 as backward, 1 as forward
     * @param int $position position index oposite to direction
     * @param $nodeValue
     */
    public static function addChildAtPosition(SimpleXMLElement $xml, $nodeName, $direction, $position, $nodeValue)
    {
        $z = $xml->asXML();
        if (!$z) {
            $xml->addChild('fake');
        }

        $dom = dom_import_simplexml($xml);
        $x = $dom->ownerDocument->saveXML($dom);

        $hasChildren = $dom->hasChildNodes();

        if ($hasChildren) {
            $childCount = $dom->childNodes->length;

            $newNode = $xml->addChild($nodeName, $nodeValue);
            $newNodeDom = $dom->ownerDocument->importNode(dom_import_simplexml($newNode), true);
            if ($position <= $childCount) {
                if (!$direction) {
                    $position = $childCount - $position;
                } else {
                    $x = 1;
                }
                $target = $dom->childNodes->item($position);
                $dom->insertBefore($newNodeDom, $target);
            }

            return;
        } else {
            $xml->addChild($nodeName, $nodeValue);
        }
        //throw new Exception('object hasnt children');

    }

    public static function castToXmlString($data)
    {
        if (is_string($data)) {
            $xml = self::asSimpleXml($data);
        } elseif ($data instanceof SimpleXMLElement) {
            $xml = $data->asXML();
        } elseif (is_array($data)) {
            $xml = self::from_array($data, 'data');
        } else {
            throw new Exception('unknown format');
        }
        return self::checkIsValidXmlStirng($xml);
    }

}