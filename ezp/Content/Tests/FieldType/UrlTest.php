<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\Url\Type as Url,
    ezp\Content\FieldType\Url\Value as UrlValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\Url\\Type",
            Factory::build( "ezurl" ),
            "Url object not returned for 'ezurl', incorrect mapping? "
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType::allowedValidators
     */
    public function testUrlSupportedValidators()
    {
        $ft = new Url();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \ezp\Content\FieldType\Url\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @group fieldType
     */
    public function testCanParseValueInvalidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new UrlValue( 42 ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Url\Type::canParseValue
     */
    public function testCanParseValueValidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $value = new UrlValue( "http://ez.no/" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Url\Type::toFieldValue
     */
    public function testToFieldValue()
    {
        $link = "http://ez.no/";
        $ft = new Url();
        $ft->setValue( $fv = new UrlValue( $link ) );

        $fieldValue = $ft->toFieldValue();

        self::assertSame( $fv, $fieldValue->data );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\FieldSettings', $fieldValue->fieldSettings );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Url\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, $value->link );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Url\Value::fromString
     */
    public function testBuildFieldValueFromString()
    {
        $link = "http://ez.no/";
        $value = UrlValue::fromString( $link );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Url\\Value", $value );
        self::assertSame( $link, $value->link );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Url\Value::__toString
     */
    public function testFieldValueToString()
    {
        $link = "http://ez.no/";
        $value = UrlValue::fromString( $link );
        self::assertSame( $link, (string)$value );

        self::assertSame(
            $link,
            UrlValue::fromString( (string)$value )->link,
            "fromString() and __toString() must be compatible"
        );
    }
}
