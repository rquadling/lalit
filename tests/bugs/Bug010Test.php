<?php

namespace LaLit;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class Bug0088Test.
 */
class Bug010Test extends TestCase
{
    /**
     * @param string $xml
     * @param array $array
     *
     * @throws Exception
     *
     * @dataProvider provideNamespacedData
     *
     */
    public function testNamespaceHandling(string $xml, array $array)
    {
        $actualXMLResults = Array2XML::createXML('root', $array['root'] ?? [], $array['@docType'] ?? [])->saveXML();
        $actualArrayResults = XML2Array::createArray($xml);

        $this->assertEquals($array, $actualArrayResults, '');
        $this->assertEquals($xml, $actualXMLResults, '');
    }

    /**
     * @param string $xml
     * @param array $array
     *
     * @throws Exception
     *
     * @dataProvider provideNamespacedDataWithAllNamespaces()
     *
     */
    public function testNamespaceHandlingWithAllNamespaces(string $xml, array $array)
    {
        // Make sure all namespaces are output, include the current defaulting xmlns:xml.
        XML2Array::init('1.0', 'utf-8', false, true, true);
        $actualXMLResults = Array2XML::createXML('root', $array['root'] ?? [], $array['@docType'] ?? [])->saveXML();
        $actualArrayResults = XML2Array::createArray($xml);

        $this->assertEquals($array, $actualArrayResults, '');
        $this->assertEquals($xml, $actualXMLResults, '');
    }

    /**
     * @param string $originalXml
     * @param array $array
     * @param string $newXml
     *
     * @throws Exception
     *
     * @dataProvider provideNamespacedDataWithAllNamespacesWithDefaults()
     *
     */
    public function testNamespaceHandlingWithAllNamespacesDefaults(string $originalXml, array $array, string $newXml)
    {
        // Make sure all namespaces are output, include the current defaulting xmlns:xml.
        XML2Array::init('1.0', 'utf-8', false, true, true);
        $actualXMLResults = Array2XML::createXML('root', $array['root'] ?? [], $array['@docType'] ?? [])->saveXML();
        $actualArrayResults = XML2Array::createArray($originalXml);

        $this->assertEquals($array, $actualArrayResults, '');
        $this->assertEquals($newXml, $actualXMLResults, '');
    }

    public function testExceptionIsThrownWhenUsingAnInvalidUriForTheXmlNamespace()
    {
        $array = [
            'root' => [
                '@attributes' => [
                    'xmlns:xml' => 'http://example.com/xmlns',
                ],
            ],
        ];

        // Generated XML from the above array - but is invalid as the xmlns:xml URI must be "http://www.w3.org/XML/1998/namespace"
        $array2xml = <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://example.com/xmlns"/>

END_XML;

        XML2Array::init('1.0', 'utf-8', false, false, true);

        $this->assertEquals($array2xml, Array2XML::createXML('root', $array['root'])->saveXML());

        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('`\[XML2Array\] Error parsing the XML string.\s+DOMDocument::loadXML\(\): xml namespace prefix mapped to wrong URI in Entity`');
        XML2Array::createArray($array2xml);
    }

    public function provideNamespacedData()
    {
        return
            [
                'No namespaces' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root root_attribute="root_attribute_value">
  <container>
    <item present="none" zero="0">
      <term>description</term>
      <label></label>
      <zero>0</zero>
      <zeroCData><![CDATA[0]]></zeroCData>
      <node present="years" empty="" zero="0">0</node>
    </item>
  </container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'container' => [
                                'item' => [
                                    'term' => 'description',
                                    'label' => null,
                                    'zero' => 0,
                                    'zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'present' => 'none',
                                        'zero' => 0,
                                    ],
                                    'node' => [
                                        '@attributes' => [
                                            'present' => 'years',
                                            'empty' => null,
                                            'zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
                'With XML namespace' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xml:root_attribute="root_attribute_value">
  <xml:container>
    <xml:item xml:present="none" xml:zero="0">
      <xml:term>description</xml:term>
      <xml:label></xml:label>
      <xml:zero>0</xml:zero>
      <xml:zeroCData><![CDATA[0]]></xml:zeroCData>
      <xml:node xml:present="years" xml:empty="" xml:zero="0">0</xml:node>
    </xml:item>
  </xml:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'xml:container' => [
                                'xml:item' => [
                                    'xml:term' => 'description',
                                    'xml:label' => null,
                                    'xml:zero' => 0,
                                    'xml:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'xml:present' => 'none',
                                        'xml:zero' => 0,
                                    ],
                                    'xml:node' => [
                                        '@attributes' => [
                                            'xml:present' => 'years',
                                            'xml:empty' => null,
                                            'xml:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xml:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
                'With LaLit namespace' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:lalit="http://www.digitickets.co.uk/lalit" lalit:root_attribute="root_attribute_value">
  <lalit:container>
    <lalit:item lalit:present="none" lalit:zero="0">
      <lalit:term xml:lang="en-GB">description</lalit:term>
      <lalit:label></lalit:label>
      <lalit:zero>0</lalit:zero>
      <lalit:zeroCData><![CDATA[0]]></lalit:zeroCData>
      <lalit:node lalit:present="years" lalit:empty="" lalit:zero="0">0</lalit:node>
    </lalit:item>
  </lalit:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'lalit:container' => [
                                'lalit:item' => [
                                    'lalit:term' => [
                                        '@value' => 'description',
                                        '@attributes' => [
                                            'xml:lang' => 'en-GB',
                                        ],
                                    ],
                                    'lalit:label' => null,
                                    'lalit:zero' => 0,
                                    'lalit:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'lalit:present' => 'none',
                                        'lalit:zero' => 0,
                                    ],
                                    'lalit:node' => [
                                        '@attributes' => [
                                            'lalit:present' => 'years',
                                            'lalit:empty' => null,
                                            'lalit:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:lalit' => 'http://www.digitickets.co.uk/lalit',
                                'lalit:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function provideNamespacedDataWithAllNamespaces()
    {
        return
            [
                'No namespaces except the default xmlsns:xml namespace' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" root_attribute="root_attribute_value">
  <container>
    <item present="none" zero="0">
      <term>description</term>
      <label></label>
      <zero>0</zero>
      <zeroCData><![CDATA[0]]></zeroCData>
      <node present="years" empty="" zero="0">0</node>
    </item>
  </container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'container' => [
                                'item' => [
                                    'term' => 'description',
                                    'label' => null,
                                    'zero' => 0,
                                    'zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'present' => 'none',
                                        'zero' => 0,
                                    ],
                                    'node' => [
                                        '@attributes' => [
                                            'present' => 'years',
                                            'empty' => null,
                                            'zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
                'With XML namespace' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" xml:root_attribute="root_attribute_value">
  <xml:container>
    <xml:item xml:present="none" xml:zero="0">
      <xml:term>description</xml:term>
      <xml:label></xml:label>
      <xml:zero>0</xml:zero>
      <xml:zeroCData><![CDATA[0]]></xml:zeroCData>
      <xml:node xml:present="years" xml:empty="" xml:zero="0">0</xml:node>
    </xml:item>
  </xml:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'xml:container' => [
                                'xml:item' => [
                                    'xml:term' => 'description',
                                    'xml:label' => null,
                                    'xml:zero' => 0,
                                    'xml:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'xml:present' => 'none',
                                        'xml:zero' => 0,
                                    ],
                                    'xml:node' => [
                                        '@attributes' => [
                                            'xml:present' => 'years',
                                            'xml:empty' => null,
                                            'xml:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'xml:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
                'With LaLit namespace' => [
                    'xml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" xmlns:lalit="http://www.digitickets.co.uk/lalit" lalit:root_attribute="root_attribute_value">
  <lalit:container>
    <lalit:item lalit:present="none" lalit:zero="0">
      <lalit:term xml:lang="en-GB">description</lalit:term>
      <lalit:label></lalit:label>
      <lalit:zero>0</lalit:zero>
      <lalit:zeroCData><![CDATA[0]]></lalit:zeroCData>
      <lalit:node lalit:present="years" lalit:empty="" lalit:zero="0">0</lalit:node>
    </lalit:item>
  </lalit:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'lalit:container' => [
                                'lalit:item' => [
                                    'lalit:term' => [
                                        '@value' => 'description',
                                        '@attributes' => [
                                            'xml:lang' => 'en-GB',
                                        ],
                                    ],
                                    'lalit:label' => null,
                                    'lalit:zero' => 0,
                                    'lalit:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'lalit:present' => 'none',
                                        'lalit:zero' => 0,
                                    ],
                                    'lalit:node' => [
                                        '@attributes' => [
                                            'lalit:present' => 'years',
                                            'lalit:empty' => null,
                                            'lalit:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'xmlns:lalit' => 'http://www.digitickets.co.uk/lalit',
                                'lalit:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function provideNamespacedDataWithAllNamespacesWithDefaults()
    {
        return
            [
                'No namespaces' => [
                    'originalXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root root_attribute="root_attribute_value">
  <container>
    <item present="none" zero="0">
      <term>description</term>
      <label></label>
      <zero>0</zero>
      <zeroCData><![CDATA[0]]></zeroCData>
      <node present="years" empty="" zero="0">0</node>
    </item>
  </container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'container' => [
                                'item' => [
                                    'term' => 'description',
                                    'label' => null,
                                    'zero' => 0,
                                    'zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'present' => 'none',
                                        'zero' => 0,
                                    ],
                                    'node' => [
                                        '@attributes' => [
                                            'present' => 'years',
                                            'empty' => null,
                                            'zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                    'newXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" root_attribute="root_attribute_value">
  <container>
    <item present="none" zero="0">
      <term>description</term>
      <label></label>
      <zero>0</zero>
      <zeroCData><![CDATA[0]]></zeroCData>
      <node present="years" empty="" zero="0">0</node>
    </item>
  </container>
</root>

END_XML
                    ,
                ],
                'With XML namespace' => [
                    'originalXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" xml:root_attribute="root_attribute_value">
  <xml:container>
    <xml:item xml:present="none" xml:zero="0">
      <xml:term>description</xml:term>
      <xml:label></xml:label>
      <xml:zero>0</xml:zero>
      <xml:zeroCData><![CDATA[0]]></xml:zeroCData>
      <xml:node xml:present="years" xml:empty="" xml:zero="0">0</xml:node>
    </xml:item>
  </xml:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'xml:container' => [
                                'xml:item' => [
                                    'xml:term' => 'description',
                                    'xml:label' => null,
                                    'xml:zero' => 0,
                                    'xml:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'xml:present' => 'none',
                                        'xml:zero' => 0,
                                    ],
                                    'xml:node' => [
                                        '@attributes' => [
                                            'xml:present' => 'years',
                                            'xml:empty' => null,
                                            'xml:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'xml:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                    'newXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" xml:root_attribute="root_attribute_value">
  <xml:container>
    <xml:item xml:present="none" xml:zero="0">
      <xml:term>description</xml:term>
      <xml:label></xml:label>
      <xml:zero>0</xml:zero>
      <xml:zeroCData><![CDATA[0]]></xml:zeroCData>
      <xml:node xml:present="years" xml:empty="" xml:zero="0">0</xml:node>
    </xml:item>
  </xml:container>
</root>

END_XML
                    ,
                ],
                'With LaLit namespace' => [
                    'originalXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:lalit="http://www.digitickets.co.uk/lalit" lalit:root_attribute="root_attribute_value">
  <lalit:container>
    <lalit:item lalit:present="none" lalit:zero="0">
      <lalit:term xml:lang="en-GB">description</lalit:term>
      <lalit:label></lalit:label>
      <lalit:zero>0</lalit:zero>
      <lalit:zeroCData><![CDATA[0]]></lalit:zeroCData>
      <lalit:node lalit:present="years" lalit:empty="" lalit:zero="0">0</lalit:node>
    </lalit:item>
  </lalit:container>
</root>

END_XML
                    ,
                    'array' => [
                        'root' => [
                            'lalit:container' => [
                                'lalit:item' => [
                                    'lalit:term' => [
                                        '@value' => 'description',
                                        '@attributes' => [
                                            'xml:lang' => 'en-GB',
                                        ],
                                    ],
                                    'lalit:label' => null,
                                    'lalit:zero' => 0,
                                    'lalit:zeroCData' => [
                                        '@cdata' => 0,
                                    ],
                                    '@attributes' => [
                                        'lalit:present' => 'none',
                                        'lalit:zero' => 0,
                                    ],
                                    'lalit:node' => [
                                        '@attributes' => [
                                            'lalit:present' => 'years',
                                            'lalit:empty' => null,
                                            'lalit:zero' => 0,
                                        ],
                                        '@value' => 0,
                                    ],
                                ],
                            ],
                            '@attributes' => [
                                'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace',
                                'xmlns:lalit' => 'http://www.digitickets.co.uk/lalit',
                                'lalit:root_attribute' => 'root_attribute_value',
                            ],
                        ],
                    ],
                    'newXml' => <<< 'END_XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<root xmlns:xml="http://www.w3.org/XML/1998/namespace" xmlns:lalit="http://www.digitickets.co.uk/lalit" lalit:root_attribute="root_attribute_value">
  <lalit:container>
    <lalit:item lalit:present="none" lalit:zero="0">
      <lalit:term xml:lang="en-GB">description</lalit:term>
      <lalit:label></lalit:label>
      <lalit:zero>0</lalit:zero>
      <lalit:zeroCData><![CDATA[0]]></lalit:zeroCData>
      <lalit:node lalit:present="years" lalit:empty="" lalit:zero="0">0</lalit:node>
    </lalit:item>
  </lalit:container>
</root>

END_XML
                    ,
                ],
            ];
    }
}
