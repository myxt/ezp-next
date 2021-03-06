<?php
/**
 * File containing the ezp\Content\Tests\FieldType\XmlText\InputHandlerTest class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType\XmlText;

use ezp\Content\FieldType\XmlText\Input\Parser\Simplified as Parser,
    ezp\Content\Relation,

    PHPUnit_Framework_TestCase,
    DOMDocument;

class SimplifiedTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new Parser();
        $handler = $this
            ->getMockBuilder( 'ezp\\Content\\FieldType\\XmlText\\Input\\Handler' )
            ->setConstructorArgs( array( $this->parser ) )
            ->getMock();
    }

    /**
     * @dataProvider providerForTestProcess
     */
    public function testProcess( $xmlString, $domString )
    {
        $document = $this->parser->process( $xmlString );
        self::assertEquals( $domString, $document->saveXML() );
    }

    public function providerForTestProcess()
    {
        return array( array( '', '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"/>
' ) );
    }
    /**
     * @var \ezp\Content\FieldType\XmlText\Input\Parser
     */
    private $parser;
}
