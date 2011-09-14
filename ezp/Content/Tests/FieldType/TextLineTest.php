<?php
/**
 * File containing the TextLineTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use PHPUnit_Framework_TestCase,
    ezp\Content\FieldType,
    ezp\Content\FieldType\TextLine,
    ReflectionClass,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Persistence\Content\FieldValue,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\Validator\StringLengthValidator;

class TextLineTest extends PHPUnit_Framework_TestCase
{
    public function testTypeString()
    {
        $ft = new TextLine();
        self::assertEquals( 'ezstring', $ft->type() );
    }

    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     */
    public function testFactory()
    {
        $text = FieldType::create( 'ezstring' );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine', $text, "TextLine object not returned for 'ezstring', incorrect mapping? " );
    }

    public function testTextLineSupportsSearch()
    {
        $ft = new TextLine();
        self::assertTrue( $ft->supportsSearch(), "TextLine should report support for search." );

        $ref = new ReflectionClass( 'ezp\\Content\FieldType\\TextLine' );
        $searchProperty = $ref->getProperty( 'isSearchable' );
        $searchProperty->setAccessible( true );
        self::assertTrue ( $searchProperty->getValue( $ft ), "The internal search attribute is not set correctly." );
    }

    public function testTextLineSupportedValidators()
    {
        $ft = new TextLine();
        self::assertSame( array( 'StringLengthValidator' ), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     */
    public function testInvalidFormat()
    {
        $ft = new TextLine();
        $ft->setValue( 42 );
    }

    public function testValidFormat()
    {
        $ft = new TextLine();
        $value = 'Strings works just fine.';
        $ft->setValue( 'Strings works just fine.' );
        self::assertEquals( $value, $ft->getValue() );
    }

    public function testHandlerIsAsExpected()
    {
        $ft = new TextLine();
        self::assertNull( $ft->getHandler(), "TextLine shouldn't have a handler" );
    }

    public function testSetFieldValue()
    {
        $ft = new TextLine();
        $ft->setValue( 'Test of FieldValue' );

        $fieldValue = new FieldValue();
        $ft->setFieldValue( $fieldValue );

        self::assertSame( array( 'value' => 'Test of FieldValue' ), $fieldValue->data );
        self::assertNull( $fieldValue->externalData );
        self::assertSame( array( 'sort_key_string' => 'Test of FieldValue' ), $fieldValue->sortKey );
    }
}