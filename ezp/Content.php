<?php
/**
 * File containing the ezp\Content\Concrete interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Content\Section,
    ezp\Content\Location,
    ezp\Base\ModelDefinition,
    ezp\User;

/**
 * This interface represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read mixed $id The Content's ID, automatically assigned by the persistence layer
 * @property-read int $currentVersionNo The Content's current version
 * @property-read int $status The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property string[] $name The Content's name
 * @property-read mixed $ownerId Id of the user object that owns the content
 * @property-read bool $alwaysAvailable The Content's always available flag
 * @property-read string $remoteId The Content's remote identifier (custom identifier for the object)
 * @property-read mixed $sectionId Read property for section id, use with object $section to change
 * @property-read mixed $typeId Read property for type id
 * @property-read \ezp\Content\Type $contentType The Content's type
 * @property-read \ezp\Content\Version[] $versions
 *                Iterable collection of versions for content. Array-accessible :;
 *                <code>
 *                $myFirstVersion = $content->versions[1];
 *                $myThirdVersion = $content->versions[3];
 *                </code>
 * @property-read \ezp\Content\Version $currentVersion Current version of content
 * @property-read \ezp\Content\Location $mainLocation
 * @property-read \ezp\Content\Location[] $locations
 *                Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                First location referenced in the collection represents the main location for content
 *                <code>
 *                $mainLocation = $content->locations[0];
 *                $anotherLocation = $content->locations[2];
 *                $locationById = $content->locations->byId( 60 );
 *                </code>
 * @property-read DateTime $creationDate The date the object was created
 * @property-read \ezp\Content\Section $section The Section the content belongs to
 * @property-read \ezp\Content\Relation[] $relations Collection of \ezp\Content\Relation objects, related to the current one
 * @property-read \ezp\Content\Relation[] $reverseRelations Collection of \ezp\Content\Relation objects, reverse-related to the current one
 * @property-read \ezp\Content\Translation[] $translations
 *           Collection of content's translations, indexed by locale (ie. eng-GB)
 *           <code>
 *           $myEnglishTranslation = $content->translations["eng-GB"];
 *           $myEnglishTitle = $content->translations["eng-GB"]->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property-read \ezp\Content\Field[] $fields
 *           Collection of content's fields in default (current) language.
 *           Shorthand property to directly access to the content's fields in current language
 *           <code>
 *           $myTitle = $content->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property-read int $ownerId
 *           Owner identifier
 * @property \ezp\User $owner
 *           Owner user object
 * @property-read mixed $initialLanguageId
 *                The id of the language the Content was initially created in. Set using {@see setInitialLanguage()}
 * @property \ezp\Content\Language $initialLanguage
 *           The language the Content was initially created in
 */
interface Content extends ModelDefinition
{
    /**
     * Publication status constants
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * Return Main location object on this Content object
     *
     * @return \ezp\Content\Location|null
     */
    public function getMainLocation();

    /**
     * Return a collection containing all available versions of the Content
     *
     * @return \ezp\Content\Version[]
     */
    public function getVersions();

    /**
     * Find current version amongst version objects
     *
     * @return \ezp\Content\Version|null
     */
    public function getCurrentVersion();

    /**
     * Return Type object
     *
     * @return \ezp\Content\Type
     */
    public function getContentType();

    /**
     * Get fields of current version
     *
     * @todo Do we really want/need this shortcut?
     * @return \ezp\Content\Field[]
     */
    public function getFields();

    /**
     * Sets the Section the Content belongs to
     *
     * @param \ezp\Content\Section $section
     */
    public function setSection( Section $section );

    /**
     * Returns the Section the Content belongs to
     *
     * @return \ezp\Content\Section
     */
    public function getSection();

    /**
     * Sets the Owner the Content belongs to
     *
     * @param \ezp\User $owner
     */
    public function setOwner( User $owner );

    /**
     * Returns the User the Content is owned by
     *
     * @return \ezp\User
     */
    public function getOwner();

    /**
     * Adds a new location to content under an existing one.
     *
     * @param \ezp\Content\Location $parentLocation
     * @return \ezp\Content\Location
     */
    public function addParent( Location $parentLocation );

    /**
     * Gets locations
     *
     * @return \ezp\Content\Location[]
     */
    public function getLocations();

    /**
     * Gets Content relations
     *
     * @return \ezp\Content[]
     */
    public function getRelations();

    /**
     * Gets Content reverse relations
     *
     * @return \ezp\Content[]
     */
    public function getReverseRelations();
}
