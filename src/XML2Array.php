<?php

namespace LaLit;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMXPath;
use Exception;

/**
 * XML2Array: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 *
 * Usage:
 *       $array = XML2Array::createArray($xml);
 */
class XML2Array
{
    /**
     * @var string
     */
    private static $encoding = 'UTF-8';

    /**
     * @var bool
     */
    private static $include_all_namespaces = false;

    /**
     * @var DOMDocument
     */
    private static $xml = null;

    /**
     * Convert an XML to Array.
     *
     * @param string|DOMDocument $input_xml
     *
     * @return array
     *
     * @throws Exception
     */
    public static function createArray($input_xml)
    {
        $xml = self::getXMLRoot();
        if (is_string($input_xml)) {
            try {
                $xml->loadXML($input_xml);
                if (!is_object($xml) || empty($xml->documentElement)) {
                    throw new Exception();
                }
            } catch (Exception $ex) {
                throw new Exception('[XML2Array] Error parsing the XML string.'.PHP_EOL.$ex->getMessage());
            }
        } elseif (is_object($input_xml)) {
            if (get_class($input_xml) != 'DOMDocument') {
                throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
            }
            $xml = self::$xml = $input_xml;
        } else {
            throw new Exception('[XML2Array] Invalid input');
        }

        // Bug 008 - Support <!DOCTYPE>.
        $docType = $xml->doctype;
        if ($docType) {
            $array['@docType'] = [
                'name' => $docType->name,
                'entities' => self::getNamedNodeMapAsArray($docType->entities),
                'notations' => self::getNamedNodeMapAsArray($docType->notations),
                'publicId' => $docType->publicId,
                'systemId' => $docType->systemId,
                'internalSubset' => $docType->internalSubset,
            ];
        }

        $array[$xml->documentElement->tagName] = self::convert($xml->documentElement);

        // Bug 010 - Support root node namespaces.
        $xpath = new DOMXPath($xml);
        foreach($xpath->query('namespace::*', $xml->documentElement) as $xPathNode) {
            // Ignore the default xml namespace for xml - potentially a problem
            if (self::$include_all_namespaces || !($xPathNode->nodeName === 'xmlns:xml' && $xPathNode->nodeValue === 'http://www.w3.org/XML/1998/namespace')) {
                $array[$xml->documentElement->nodeName]['@attributes'][$xPathNode->nodeName] = $xPathNode->nodeValue;
            }
        }

        self::$xml = null;    // clear the xml node in the class for 2nd time use.

        return $array;
    }

    /**
     * Initialize the root XML node [optional].
     *
     * @param string $version
     * @param string $encoding
     * @param bool   $standalone
     * @param bool   $format_output
     * @param bool   $include_all_namespaces
     */
    public static function init(
        string $version = '1.0',
        string $encoding = 'utf-8',
        bool $standalone = false,
        bool $format_output = true,
        bool $include_all_namespaces = false)
    {
        self::$xml = new DomDocument($version, $encoding);
        self::$xml->xmlStandalone = $standalone;
        self::$xml->formatOutput = $format_output;
        self::$encoding = $encoding;
        self::$include_all_namespaces = $include_all_namespaces;
    }

    /**
     * Convert an XML to an Array.
     *
     * @param DOMNode $node - XML as a string or as an object of DOMDocument
     *
     * @return array
     */
    private static function convert(DOMNode $node)
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output['@cdata'] = trim($node->textContent);
                break;

            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:
                // for each child node, call the convert function recursively
                for ($i = 0, $m = $node->childNodes->length; $i < $m; ++$i) {
                    $child = $node->childNodes->item($i);
                    $v = self::convert($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;

                        // assume more nodes of same kind are coming
                        if (!array_key_exists($t, $output)) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } else {
                        //check if it is not an empty node
                        if ($v !== '') {
                            $output = $v;
                        }
                    }
                }

                if (is_array($output)) {
                    // if only one node of its kind, assign it directly instead if array($value);
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }
                    if (empty($output)) {
                        //for empty nodes
                        $output = '';
                    }
                }

                // loop through the attributes and collect them
                if ($node->attributes->length) {
                    $a = [];
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrNode->nodeName] = $attrNode->value;
                    }
                    // if it's a leaf node, store the value in @value instead of directly storing it.
                    if (!is_array($output)) {
                        $output = ['@value' => $output];
                    }
                    $output['@attributes'] = $a;
                }
                break;
        }

        return $output;
    }

    /**
     * Get the root XML node, if there isn't one, create it.
     *
     * @return DOMDocument
     */
    private static function getXMLRoot()
    {
        if (empty(self::$xml)) {
            self::init();
        }

        return self::$xml;
    }

    /**
     * @param DOMNamedNodeMap $namedNodeMap
     *
     * @return array|null
     */
    private static function getNamedNodeMapAsArray(DOMNamedNodeMap $namedNodeMap)
    {
        $result = null;
        if ($namedNodeMap->length) {
            foreach ($namedNodeMap as $key => $entity) {
                $result[$key] = $entity;
            }
        }

        return $result;
    }
}
