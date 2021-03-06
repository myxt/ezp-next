<?php
/**
 * File containing the BinaryFile Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\BinaryFile;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Io\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = "ezbinaryfile";
    const IS_SEARCHABLE = true;

    protected $allowedValidators = array(
        'ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\BinaryFile\Value
     */
    protected function getDefaultValue()
    {
        return new Value;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\BinaryFile\Value $inputValue
     * @return \ezp\Content\FieldType\BinaryFile\Value
     */
    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\BinaryFile\\Value' );
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
     */
    protected function getSortInfo()
    {
        return false;
    }
}
