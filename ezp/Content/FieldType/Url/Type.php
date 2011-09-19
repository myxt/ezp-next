<?php
/**
 * File containing the Url class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Url;
use ezp\Content\FieldType\Complex,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\Url\Value as UrlValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Content\Type\FieldDefinition;

/**
 * The Url field type.
 *
 * This field type represents a simple string.
 */
class Type extends Complex
{
    const FIELD_TYPE_IDENTIFIER = "ezurl";
    const IS_SEARCHABLE = false;

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @todo Is a default value really possible with this type?
     *       Shouldn't an exception be used?
     * @return \ezp\Content\FieldType\Url\Value
     */
    protected function getDefaultValue()
    {
        return new Value( "" );
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\Url\Value $inputValue
     * @return \ezp\Content\FieldType\Url\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof UrlValue || !is_string( $inputValue->link ) )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }
        return $inputValue;
    }

    /**
     * Returns a handler, aka. a helper object which aids in the manipulation of
     * complex field type values.
     *
     * @return void|ezp\Content\FieldType\Handler
     */
    public function getHandler()
    {
        return new Handler();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo Sort seems to not be supported by this FieldType, is this handled correctly?
     * @return array
     */
    protected function getSortInfo()
    {
        return array(
            'sort_key_string' => '',
            'sort_key_int' => 0
        );
    }

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return array
     */
    protected function getValueData()
    {
        return array(
            "link" => $this->getValue()->link,
            "text" => $this->getValue()->text,
        );
    }

    /**
     * Returns the external value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @return null|array
     * @todo Shouldn't it return a struct with appropriate properties instead of an array ?
     */
    public function getValueExternalData()
    {
    }
}