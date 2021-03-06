<?php
/**
 * File containing the UpdateStruct struct
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 */
class UpdateStruct extends ValueObject
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var int
     */
    public $versionNo;

    /**
     * @var string[] Eg. array( 'eng-GB' => "New Article" )
     */
    public $name = array();

    /**
     * @var int Creator of the new version
     */
    public $creatorId;

    /**
     * @var int Owner id of the content object
     */
    public $ownerId;

    /**
     * Contains fields to be updated.
     *
     * @var \ezp\Persistence\Content\Field[]
     */
    public $fields = array();

    /**
     * Publication date, only used by publish()
     * @var int Unix timestamp
     */
    public $published;

    /**
     * Modification date
     * @var int Unix timestamp
     */
    public $modified;

    /**
     * TODO: Document
     *
     * @var mixed
     */
    public $initialLanguageId = false;
}
?>
