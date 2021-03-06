<?php
/**
 * File containing the Content Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Content\Handler as BaseContentHandler,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * The Content Handler stores Content and ContentType objects.
 */
class Handler implements BaseContentHandler
{
    /**
     * Content gateway.
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Location gateway.
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mapper.
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * FieldHandler
     *
     * @var \ezp\Persistence\Storage\Legacy\FieldHandler
     */
    protected $fieldHandler;

    /**
     * Creates a new content handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     * @todo Fix param docs
     */
    public function __construct(
        Gateway $contentGateway,
        Location\Gateway $locationGateway,
        Mapper $mapper,
        FieldHandler $fieldHandler
    )
    {
        $this->contentGateway = $contentGateway;
        $this->locationGateway = $locationGateway;
        $this->mapper = $mapper;
        $this->fieldHandler = $fieldHandler;
    }

    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \ezp\Persistence\Content\CreateStruct $struct Content creation struct.
     * @return \ezp\Persistence\Content Content value object
     */
    public function create( CreateStruct $struct )
    {
        $content = $this->mapper->createContentFromCreateStruct(
            $struct
        );
        $content->id = $this->contentGateway->insertContentObject(
            $content, $struct->fields
        );

        $content->version = $this->mapper->createVersionForContent( $content, 1 );
        $content->version->id = $this->contentGateway->insertVersion(
            $content->version, $struct->fields, $content->alwaysAvailable
        );
        $content->version->fields = $struct->fields;

        $this->fieldHandler->createNewFields( $content );

        foreach ( $struct->locations as $location )
        {
            $this->locationGateway->createNodeAssignment(
                $this->mapper->createLocationCreateStruct( $content ),
                $location->parentId,
                Location\Gateway::NODE_ASSIGNMENT_OP_CODE_CREATE
            );
        }

        return $content;
    }

    /**
     * Performs the publishing operations required to set the version identified by $updateStruct->versionNo and
     * $updateStruct->id as the published one.
     *
     * The UpdateStruct will also contain an array of Content name indexed by Locale.
     *
     * The publish procedure will:
     * - Create location nodes based on the node assignments
     * - Create the entry in the ezcontentobject_name table
     * - Updates the content object using the provided update struct
     * - Updates the node assignments
     *
     * @param \ezp\Persistence\Content\UpdateStruct An UpdateStruct with id, versionNo and name array
     * @return \ezp\Persistence\Content The published Content
     */
    public function publish( UpdateStruct $updateStruct )
    {
        $content = $this->update( $updateStruct );

        foreach ( $updateStruct->name as $language => $name )
        {
            $this->contentGateway->setName(
                $updateStruct->id,
                $updateStruct->versionNo,
                $name, $language
            );
        }

        $this->locationGateway->createLocationsFromNodeAssignments(
            $updateStruct->id,
            $updateStruct->versionNo
        );
        return $content;
    }

    /**
     * Creates a new draft version from $contentId in $version.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * Note: When creating a new draft in the old admin interface there will
     * also be an entry in the `eznode_assignment` created for the draft. This
     * is ignored in this implementation.
     *
     * @param int $contentId
     * @param int|bool $srcVersion
     * @return \ezp\Persistence\Content\Version
     */
    public function createDraftFromVersion( $contentId, $srcVersion )
    {
        $content = $this->load( $contentId, $srcVersion );

        // Create new version
        $content->version = $this->mapper->createVersionForContent(
            $content,
            $content->version->versionNo + 1
        );

        $content->version->id = $this->contentGateway->insertVersion(
            $content->version,
            $content->version->fields,
            $content->alwaysAvailable
        );

        $this->fieldHandler->createNewFields( $content );

        return $content->version;
    }

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * A version to load must be specified. If you want to load the current
     * version of a content object use SearchHandler::findSingle() with the
     * ContentId criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param int|string $id
     * @param int|string $version
     * @param string[] $translations
     * @return \ezp\Persistence\Content Content value object
     */
    public function load( $id, $version, $translations = null )
    {
        $rows = $this->contentGateway->load( $id, $version, $translations );

        if ( !count( $rows ) )
        {
            throw new \ezp\Base\Exception\NotFound( 'content', $id );
        }

        $contentObjects = $this->mapper->extractContentFromRows( $rows );
        $content = $contentObjects[0];

        $this->fieldHandler->loadExternalFieldData( $content );

        return $content;
    }

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     * @todo Is this supposed to be constants from Content or Version? They differ..
     *
     * @param int $contentId
     * @param int $status
     * @param int $version
     * @see ezp\Content
     * @return boolean
     */
    public function setStatus( $contentId, $status, $version )
    {
        return $this->contentGateway->setStatus( $contentId, $version, $status );
    }

    /**
     * Sets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @param mixed $state
     * @return boolean
     * @see ezp\Content
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Gets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @return mixed
     * @see ezp\Content
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Updates a content object entity with data and identifier $content
     *
     * @param \ezp\Persistence\Content\UpdateStruct $content
     * @return \ezp\Persistence\Content
     */
    public function update( UpdateStruct $content )
    {
        $this->contentGateway->updateContent( $content );
        $this->contentGateway->updateVersion( $content );
        $this->fieldHandler->updateFields( $content );

        return $this->load( $content->id, $content->versionNo );
    }

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. Alle subtrees of the
     * assigned nodes of this content objects are removed (recursivley).
     *
     * @param int $contentId
     * @return boolean
     */
    public function delete( $contentId )
    {
        $locationIds = $this->contentGateway->getAllLocationIds( $contentId );
        foreach ( $locationIds as $locationId )
        {
            $this->locationGateway->removeSubtree( $locationId );
        }
        $this->fieldHandler->deleteFields( $contentId );

        $this->contentGateway->deleteRelations( $contentId );
        $this->contentGateway->deleteVersions( $contentId );
        $this->contentGateway->deleteNames( $contentId );
        $this->contentGateway->deleteContent( $contentId );
    }

    /**
     * Return the versions for $contentId
     *
     * @param int $contentId
     * @return ezp\Persistence\Content\RestrictedVersion[]
     */
    public function listVersions( $contentId )
    {
        $rows = $this->contentGateway->listVersions( $contentId );
        return $this->mapper->extractVersionListFromRows( $rows );
    }

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param int $contentId
     * @param int|false $version Copy all versions if left false
     * @return \ezp\Persistence\Content
     * @throws \ezp\Base\Exception\NotFound If content or version is not found
     */
    public function copy( $contentId, $version )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Creates a copy of the latest published version of $contentId
     *
     * @param mixed $contentId
     * @return \ezp\Persistence\Content
     */
    public function createCopy( $contentId )
    {
        $rows = $this->contentGateway->loadLatestPublishedData( $contentId );

        if ( 0 == count( $rows ) )
        {
            throw new \ezp\Base\Exception\NotFound( 'content', $contentId );
        }
        $contentObjects = $this->mapper->extractContentFromRows( $rows );

        $createStruct = $this->mapper->createCreateStructFromContent(
            reset( $contentObjects )
        );
        return $this->create( $createStruct );
    }

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersion
     * and $destinationContentId with a specific $type.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param  \ezp\Persistence\Content\Relation\CreateStruct $relation
     * @return \ezp\Persistence\Content\Relation
     */
    public function addRelation( RelationCreateStruct $relation )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Removes a relation by relation Id.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param mixed $relationId
     */
    public function removeRelation( $relationId )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersion.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersion Source Content Version, null if not specified
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadRelations( $sourceContentId, $sourceContentVersion = null, $type = null )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * Only loads relations against published versions.
     *
     * @param mixed $destinationContentId Destination Content ID
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadReverseRelations( $destinationContentId, $type = null )
    {
        throw new \Exception( "@TODO: Not implemented yet." );
    }
}
?>
