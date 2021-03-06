<?php
/**
 * File containing the ezp\Content\Concrete class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Exception\Logic as LogicException,
    ezp\Base\Repository,
    ezp\Content,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Version\StaticCollection as VersionCollection,
    ezp\Content\Version\Concrete as ConcreteVersion,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId,
    ezp\Persistence\Content\Query\Criterion\SectionId as CriterionSectionId,
    ezp\Persistence\Content\Query\Criterion\UserMetadata as CriterionUserMetadata,
    ezp\Persistence\Content\Query\Criterion\LocationId as CriterionLocationId,
    ezp\Persistence\Content\Query\Criterion\Subtree as CriterionSubtree,
    ezp\Persistence\Content\Query\Criterion\Operator as CriterionOperator,
    ezp\User,
    DateTime,
    InvalidArgumentException;

/**
 * This class represents a concrete Content item
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
 * @property \ezp\User $owner
 *           Owner user object
 * @property-read mixed $initialLanguageId
 *                The id of the language the Content was initially created in. Set using {@see setInitialLanguage()}
 * @property \ezp\Content\Language $initialLanguage
 *           The language the Content was initially created in
 */
class Concrete extends Model implements Content
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'currentVersionNo' => false,
        'status' => false,
        'name' => true, // @todo: Make readOnly and generate on store event from attributes based on type nameScheme
        'ownerId' => false,
        'alwaysAvailable' => true,
        'remoteId' => true,// @todo Make readonly and deal with this internally (in all DO's)
        'sectionId' => false,
        'typeId' => false,
        // @todo missing @property doc and missing properties getter/setter implementation
        // in Content/Version/Concrete for the following properties:
        'modified' => true,
        'published' => true,
        'initialLanguageId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'creationDate' => false,
        'mainLocation' => false,
        'section' => false,
        'owner' => false,
        'fields' => true,
        'contentType' => false,
        'versions' => false,
        'locations' => true,
        //'translations' => true,
        'relations' => false,
        'reverseRelations' => false,
        'currentVersion' => false,
        'initialLanguage' => false,
    );

    /**
     * The Section the content belongs to
     *
     * @var \ezp\Content\Section
     */
    protected $section;

    /**
     * Locations collection
     *
     * @var \ezp\Content\Location[]
     */
    protected $locations;

    /**
     * Content type object that this Content object is an instance of
     *
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * Relations collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $reverseRelations;

    /**
     * Versions
     *
     * @var \ezp\Content\Version[]
     */
    protected $versions;

    /**
     * Current Version
     *
     * @var \ezp\Content\Version
     */
    protected $currentVersion;

    /**
     * Owner ( User )
     *
     * @var \ezp\User
     */
    protected $owner;

    /**
     * Initial content language
     * @var \ezp\Content\Language
     */
    protected $initialLanguage;

    /**
     * Create content based on content type object
     *
     * @param \ezp\Content\Type $contentType
     * @param \ezp\User $owner
     */
    public function __construct( Type $contentType, User $owner )
    {
        $this->properties = new ContentValue(
            array(
                'typeId' => $contentType->id,
                'status' => self::STATUS_DRAFT,
                'ownerId' => $owner->id
            )
        );
        /*
        @TODO Make sure all dynamic properties writes to value object if scalar value (creationDate (int)-> properties->created )
        */
        $this->contentType = $contentType;
        $this->owner = $owner;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        $this->relations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->reverseRelations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->currentVersion = new ConcreteVersion( $this );
        $this->versions = new VersionCollection( array( $this->currentVersion ) );
    }

    /**
     * Returns definition of the content object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        static $def = null;
        if ( $def !== null )
            return $def;

        $def = array(
            'module' => 'content',
            'functions' => array(
                // Note: Functions skipped in api: bookmark, dashboard, tipafriend and pdf
                // @todo Add StateLimitations on functions that need them when object states exists in public api
                'create' => array(
                    // Note: Limitations 'Class' & 'Section' is copied from 'read' function further bellow
                    'ParentOwner' => array(
                        // @todo Add support for $limitationsValues[0] == 2 when session support is added
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            if ( $limitationsValues[0] != 1 && $limitationsValues[0] != 2 )
                                throw new LogicException( 'Owner limitation', 'expected limitation value to be 1 or 2 but got:' . $limitationsValues[0] );

                            return $parent && $parent->getContent()->ownerId == $repository->getUser()->id;
                        },
                    ),
                    'ParentGroup' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            if ( $limitationsValues[0] != 1 )
                                throw new LogicException( 'ParentGroup limitation', 'expected limitation value to be 1 but got:' . $limitationsValues[0] );

                            if ( !$parent )
                                return false;

                            $parentContent = $parent->getContent();
                            $currentUser = $repository->getUser();
                            if ( $parentContent->ownerId == $currentUser->id )
                                return true;

                            foreach ( $parentContent->getOwner()->getGroups() as $parentGroup )
                            {
                                foreach ( $currentUser->getGroups() as $currentGroup )
                                {
                                    if ( $parentGroup->id == $currentGroup->id )
                                        return true;
                                }
                            }

                            return false;
                        },
                    ),
                    'ParentClass' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->getContent()->typeId, $limitationsValues );
                        },
                    ),
                    'ParentDepth' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->depth, $limitationsValues );
                        },
                    ),
                    'Node' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->id, $limitationsValues );
                        },
                    ),
                    'Subtree' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            if ( !$parent )
                                return false;

                            foreach ( $limitationsValues as $limitationPathString )
                            {
                                if ( $parent->pathString === $limitationPathString )
                                    return true;
                                if ( strpos( $parent->pathString, $limitationPathString ) === 0 )
                                    return true;
                            }
                            return false;
                        },
                    ),
                    'Language' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            // Note: Copied to other functions further down
                            // @todo: $limitationsValues is a list of languageCodes, so it needs to be matched against
                            //        language of content somehow when that api is in place
                            return false;
                        },
                    ),
                ),
                'read' => array(
                    // Note: All limitations copied to other functions further bellow
                    'Class' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            return in_array( $content->typeId, $limitationsValues );
                        },
                        'query' => function( array $limitationsValues )
                        {
                            if ( !isset( $limitationsValues[1] ) )
                                return new CriterionContentTypeId( $limitationsValues[0] );

                            return new CriterionContentTypeId( $limitationsValues );
                        },
                    ),
                    'Section' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            return in_array( $content->sectionId, $limitationsValues );
                        },
                        'query' => function( array $limitationsValues )
                        {
                            if ( !isset( $limitationsValues[1] ) )
                                return new CriterionSectionId( $limitationsValues[0] );

                            return new CriterionSectionId( $limitationsValues );
                        },
                    ),
                    'Owner' => array(
                        // @todo Add support for $limitationsValues[0] == 2 when session support is added
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository )
                        {
                            if ( $limitationsValues[0] != 1 && $limitationsValues[0] != 2 )
                                throw new LogicException( 'Owner limitation', 'expected limitation value to be 1 or 2 but got:' . $limitationsValues[0] );

                            return $content->ownerId == $repository->getUser()->id;
                        },
                        'query' => function( array $limitationsValues, Repository $repository )
                        {
                            if ( $limitationsValues[0] != 1 && $limitationsValues[0] != 2 )
                                throw new LogicException( 'Owner limitation', 'expected limitation value to be 1 or 2 but got:' . $limitationsValues[0] );

                            return new CriterionUserMetadata(
                                CriterionUserMetadata::OWNER,
                                CriterionOperator::EQ,
                                $repository->getUser()->id
                            );
                        },
                    ),
                    'Group' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository )
                        {
                            if ( $limitationsValues[0] != 1 )
                                throw new LogicException( 'Group limitation', 'expected limitation value to be 1 but got:' . $limitationsValues[0] );

                            $currentUser = $repository->getUser();
                            if ( $content->ownerId == $currentUser->id )
                                return true;

                            foreach ( $content->getOwner()->getGroups() as $group )
                            {
                                foreach ( $currentUser->getGroups() as $currentGroup )
                                {
                                    if ( $group->id == $currentGroup->id )
                                        return true;
                                }
                            }

                            return false;
                        },
                        'query' => function( array $limitationsValues, Repository $repository )
                        {
                            if ( $limitationsValues[0] != 1 )
                                throw new LogicException( 'Group limitation', 'expected limitation value to be 1 but got:' . $limitationsValues[0] );

                            $groups = $repository->getUser()->getGroups();
                            if ( !isset( $groups[1] ) )
                            {
                                return new CriterionUserMetadata(
                                    CriterionUserMetadata::GROUP,
                                    CriterionOperator::EQ,
                                    // User without user groups does not have access to content with this limitation
                                    isset( $groups[0] ) ? $groups[0]->id : 0
                                );
                            }

                            $groupIds = array();
                            foreach ( $groups as $group )
                                $groupIds[] = $group->id;

                            return new CriterionUserMetadata(
                                CriterionUserMetadata::GROUP,
                                CriterionOperator::IN,
                                $groupIds
                            );
                        },
                    ),
                    'Node' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $location = null )
                        {
                            // Use $location if provided, optionally used to check the specific location instead of all
                            // eg: 'remove' in the context of removal of a specific location
                            if ( $location instanceof Location )
                            {
                                if ( in_array( $location->id, $limitationsValues ) )
                                    return true;
                                return false;
                            }

                            foreach ( $content->locations as $location )
                            {
                                if ( in_array( $location->id, $limitationsValues ) )
                                    return true;
                            }
                            return false;
                        },
                        'query' => function( array $limitationsValues )
                        {
                            if ( !isset( $limitationsValues[1] ) )
                                return new CriterionLocationId( $limitationsValues[0] );

                            return new CriterionLocationId( $limitationsValues );
                        },
                    ),
                    'Subtree' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $location = null )
                        {
                            // Use $location if provided, optionally used to check the specific location instead of all
                            // eg: 'remove' in the context of removal of a specific location
                            if ( $location instanceof Location )
                            {
                                foreach ( $limitationsValues as $limitationPathString )
                                {
                                    if ( $location->pathString === $limitationPathString )
                                        return true;
                                    if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                                        return true;
                                }
                                return false;
                            }

                            foreach ( $content->locations as $location )
                            {
                                foreach ( $limitationsValues as $limitationPathString )
                                {
                                    if ( $location->pathString === $limitationPathString )
                                        return true;
                                    if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                                        return true;
                                }
                            }
                            return false;
                        },
                        'query' => function( array $limitationsValues )
                        {
                            if ( !isset( $limitationsValues[1] ) )
                                return new CriterionSubtree( $limitationsValues[0] );

                            return new CriterionSubtree( $limitationsValues );
                        },
                    ),
                ),
                'edit' => array(
                    // Note: Limitations copied over from 'read' + 'Language' from 'create'
                ),
                'remove' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'move' => array(),
                'versionread' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'versionremove' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'view_embed' => array(// @todo This is view stuff ,consider removing
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'diff' => array(// @todo This is view stuff ,consider removing
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'reverserelatedlist' => array(),// @todo This is view stuff, should hence be removed
                'translate' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                    // 'Language' is copied from 'create'
                ),
                'urltranslator' => array(),
                'pendinglist' => array(),// @todo This is view stuff, consider removing
                'manage_locations' => array(
                    // Note: Other limitations copied over from 'read', getting 'Group' as a bonus further down
                    'Node' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $location )
                        {
                            if ( in_array( $location->id, $limitationsValues ) )
                                return true;
                            return false;
                        },
                    ),
                    'Subtree' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $location )
                        {
                            foreach ( $limitationsValues as $limitationPathString )
                            {
                                if ( $location->pathString === $limitationPathString )
                                    return true;
                                if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                                    return true;
                            }
                            return false;
                        },
                    ),
                ),
                'hide' => array(
                    // Note: Limitations copied over from 'read' further down
                    // 'Language' is copied from 'create', & 'Subtree' + 'Node' from 'manage_locations'
                ),
                'restore' => array(),
                'cleantrash' => array(),
            ),
        );

        //// Limitations are copied to reduce duplication (never copied to 'read' as it requires 'query' support)

        // Create: Copy 'Class' & 'Section' from 'read'
        $def['functions']['create']['Class'] = $def['functions']['read']['Class'];
        $def['functions']['create']['Section'] = $def['functions']['read']['Section'];

        // Copy 'Language' from 'create'
        $def['functions']['edit']['Language'] = $def['functions']['create']['Language'];
        $def['functions']['translate']['Language'] = $def['functions']['create']['Language'];
        $def['functions']['hide']['Language'] = $def['functions']['create']['Language'];

        // Copy 'Node' & 'Subtree' from 'manage_locations'
        $def['functions']['hide']['Node'] = $def['functions']['manage_locations']['Node'];
        $def['functions']['hide']['Subtree'] = $def['functions']['manage_locations']['Subtree'];

        // Union duplicate code from 'read'
        $def['functions']['edit'] = $def['functions']['edit'] + $def['functions']['read'];
        $def['functions']['remove'] = $def['functions']['remove'] + $def['functions']['read'];
        $def['functions']['versionread'] = $def['functions']['versionread'] + $def['functions']['read'];
        $def['functions']['versionremove'] = $def['functions']['versionremove'] + $def['functions']['read'];
        $def['functions']['view_embed'] = $def['functions']['view_embed'] + $def['functions']['read'];
        $def['functions']['diff'] = $def['functions']['diff'] + $def['functions']['read'];
        $def['functions']['translate'] = $def['functions']['translate'] + $def['functions']['read'];
        $def['functions']['manage_locations'] = $def['functions']['manage_locations'] + $def['functions']['read'];
        $def['functions']['hide'] = $def['functions']['hide'] + $def['functions']['read'];

        return $def;
    }

    /**
     * Return Main location object on this Content object
     *
     * @return \ezp\Content\Location|null
     */
    public function getMainLocation()
    {
        if ( isset( $this->locations[0] ) )
            return $this->locations[0];

        return null;
    }

    /**
     * Return a collection containing all available versions of the Content
     *
     * @return \ezp\Content\Version[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Find current version amongst version objects
     *
     * @return \ezp\Content\Version|null
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Return Type object
     *
     * @return \ezp\Content\Type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get fields of current version
     * @todo Do we really want/need this shortcut?
     *
     * @return \ezp\Content\Field[]
     */
    public function getFields()
    {
        return $this->getCurrentVersion()->getFields();
    }

    /**
     * Sets the Section the Content belongs to
     *
     * @param \ezp\Content\Section $section
     */
    public function setSection( Section $section )
    {
        $this->section = $section;
        $this->properties->sectionId = $section->id;
    }

    /**
     * Returns the Section the Content belongs to
     *
     * @return \ezp\Content\Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Sets the Owner the Content belongs to
     *
     * @param \ezp\User $owner
     */
    public function setOwner( User $owner )
    {
        $this->owner = $owner;
        $this->properties->ownerId = $owner->id;
    }
    /**
     * Returns the User the Content is owned by
     *
     * @return \ezp\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Adds a new location to content under an existing one.
     *
     * @param \ezp\Content\Location $parentLocation
     * @return \ezp\Content\Location
     */
    public function addParent( Location $parentLocation )
    {
        $newLocation = new ConcreteLocation( $this );
        $newLocation->setParent( $parentLocation );
        return $newLocation;
    }

    /**
     * Gets locations
     *
     * @return \ezp\Content\Location[]
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Gets Content relations
     *
     * @return \ezp\Content\Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Gets Content reverse relations
     *
     * @return \ezp\Content\Relation[]
     */
    public function getReverseRelations()
    {
        return $this->reverseRelations;
    }

    /**
     * Sets the initial language
     * @param \ezp\Content\Language
     */
    protected function setInitialLanguage( Language $initialLanguage )
    {
        $this->initialLanguage = $initialLanguage;
        $this->properties->initialLanguageId = $initialLanguage->id;
    }

    /**
     * Gets the initial language
     * @return \ezp\Content\Language
     */
    protected function getInitialLanguage()
    {
        return $this->initialLanguage;
    }

    /**
     * Clone content object
     */
    public function __clone()
    {
        $this->properties = clone $this->properties;
        $this->properties->id = false;
        $this->properties->status = self::STATUS_DRAFT;
        // @todo make sure everything is cloned (versions / fields...) or remove these clone functions

        // Get the location's, so that new content will be the old one's sibling
        $oldLocations = $this->locations;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        foreach ( $oldLocations as $location )
        {
            $this->addParent( $location->parent );
        }
    }
}
