<?php
/**
 * File containing the InvalidAlias class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image\Exception;
use ezp\Base\Exception\InvalidArgumentValue,
    Exception as PHPException;

/**
 * Exception for invalid (not configured) image alias
 */
class InvalidAlias extends InvalidArgumentValue
{
    /**
     * Invalid alias name
     *
     * @var string
     */
    public $aliasName;

    public function __construct( $aliasName, PHPException $previous = null )
    {
        $this->aliasName = $aliasName;
        parent::InvalidArgumentValue( 'Image alias name', $aliasName, null, $previous );
    }
}
