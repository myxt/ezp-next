<?php
/**
 * File containing the Rating field type
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Rating;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput;

/**
 * Rating field types
 *
 * Represents rating.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezsrrating";
    const IS_SEARCHABLE = true;

    protected $allowedSettings = array();

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Rating\Value
     */
    protected function getDefaultValue()
    {
        return new Value();
    }

    /**
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( !$inputValue instanceof Value || !is_bool( $inputValue->isDisabled )  )
        {
            throw new BadFieldTypeInput( $inputValue, get_class() );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array(
            "sort_key_string" => "",
            "sort_key_int" => 0
        );
    }
}
