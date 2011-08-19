<?php
/**
 * File containing the ezp\Content\Type\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Collection\LazyIdList,
    ezp\Base\Collection\Lazy,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Model,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Persistence\Content\Type as TypeValue,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group as GroupValue,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\ValueObject;

/**
 * Content Service, extends repository with content specific operations
 *
 * @todo Figure out which methods should manipulate object provided or add doc on having to re fetch object.
 */
class Service extends BaseService
{
    /**
     * Crate a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @return \ezp\Content\Type\Group
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function createGroup( Group $group )
    {
        $struct = new GroupCreateStruct();
        $this->fillStruct( $struct, $group );
        $vo = $this->handler->contentTypeHandler()->createGroup( $struct  );
        return $this->buildGroup( $vo );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function updateGroup( Group $group )
    {
        $struct = new GroupUpdateStruct();
        $this->fillStruct( $struct, $group );
        $this->handler->contentTypeHandler()->updateGroup( $struct  );
    }

    /**
     * Update a Content Type Group object
     *
     * @param mixed $groupId
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function deleteGroup( $groupId )
    {
        if ( $do = $this->storage->get( 'ezp\\Content\\Type\\Group', array( 'id' => $groupId ) ) )
            $this->storage->remove( $do );
        $this->handler->contentTypeHandler()->deleteGroup( $groupId  );
    }

    /**
     * Get a Content Type Group object by id
     *
     * @param int $groupId
     * @return \ezp\Content\Type\Group
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function loadGroup( $groupId )
    {
        if ( $do = $this->storage->get( 'ezp\\Content\\Type\\Group', array( 'id' => $groupId ) ) )
            return $do;
        $vo = $this->handler->contentTypeHandler()->loadGroup( $groupId );
        if ( !$vo )
            throw new NotFound( 'Content\\Type\\Group', $groupId );
        return $this->buildGroup( $vo );
    }

    /**
     * Get all Content Type Groups
     *
     * @return \ezp\Content\Type\Group[]
     */
    public function loadAllGroups()
    {
        $list = $this->handler->contentTypeHandler()->loadAllGroups();
        foreach ( $list as $key => $vo )
        {
            if ( $do = $this->storage->get( 'ezp\\Content\\Type\\Group', array( 'id' => $vo->id ) ) )
                $list[$key] = $do;
            else
                $list[$key] = $this->buildGroup( $vo );
        }

        return $list;
    }

    /**
     * Create a Content Type object
     *
     * @param \ezp\Content\Type $type
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function create( Type $type )
    {
        $struct = new CreateStruct();
        $this->fillStruct( $struct, $type, array( 'fieldDefinitions' ) );
        foreach ( $type->fields as $field )
        {
            $struct->fieldDefinitions[] = $field->getState( 'properties' );
        }
        $vo = $this->handler->contentTypeHandler()->create( $struct  );
        return $this->buildType( $vo );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type $type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function update( Type $type )
    {
        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $type );
        $this->handler->contentTypeHandler()->update( $type->id, $type->status, $struct  );
    }

    /**
     * Delete a Content Type object
     *
     * @param int $typeId
     * @param int $status
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function delete( $typeId, $status = TypeValue::STATUS_DEFINED )
    {
        if ( $do = $this->storage->get( 'ezp\\Content\\Type', array( 'id' => $typeId, 'status' => $status ) ) )
            $this->storage->remove( $do );
        $this->handler->contentTypeHandler()->delete( $typeId, $status );
    }

    /**
     * Get a Content Type object by id
     *
     * @param int $typeId
     * @param int $status
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function load( $typeId, $status = TypeValue::STATUS_DEFINED )
    {
        if ( $do = $this->storage->get( 'ezp\\Content\\Type', array( 'id' => $typeId, 'status' => $status ) ) )
            return $do;
        return $this->buildType( $this->handler->contentTypeHandler()->load( $typeId, $status ) );
    }

    /**
     * Get Content Type objects by group Id
     *
     * @param int $groupId
     * @param int $status
     * @return \ezp\Content\Type[]
     */
    public function loadByGroupId( $groupId, $status = TypeValue::STATUS_DEFINED )
    {
        $list = $this->handler->contentTypeHandler()->loadContentTypes( $groupId, $status );
        foreach ( $list as $key => $vo )
        {
            if ( $do = $this->storage->get( 'ezp\\Content\\Type', array( 'id' => $vo->id, 'status' => $vo->status ) ) )
                $list[$key] = $do;
            else
                $list[$key] = $this->buildType( $vo );
        }

        return $list;
    }

    /**
     * Get a Content Type by identifier
     *
     * @param string $identifier
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function loadByIdentifier( $identifier )
    {
        throw new RuntimeException( "@TODO: Implement" );
    }

    /**
     * Copy Type incl fields and groupIds from a given status to a new Type with status {@link TypeValue::STATUS_DRAFT}
     *
     * New Type will have $userId as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @param mixed $userId
     * @param mixed $typeId
     * @param int $status
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If user or type with provided status is not found
     */
    public function copy( $userId, $typeId, $status = TypeValue::STATUS_DEFINED )
    {
        return $this->buildType( $this->handler->contentTypeHandler()->copy( $userId, $typeId, $status ) );
    }

    /**
     * Un-Link a content type from a group ( remove a group from a type )
     *
     * @param Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     * @throws \ezp\Base\Exception\BadRequest If $groupId is not an group on type or is the last one
     */
    public function unlink( Type $type, Group $group )
    {
        $this->handler->contentTypeHandler()->unlink( $group->id, $type->id, $type->status );
        foreach ( $type->groupIds as $key => $value )
        {
            if ( $value === $group->id )
            {
                //$type->groups->offsetUnset( $key ); does not work
                break;
            }
        }
    }

    /**
     * Link a content type to a group ( add a group to a type )
     *
     * @param Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     */
    public function link( Type $type, Group $group  )
    {
        $this->handler->contentTypeHandler()->link( $group->id, $type->id, $type->status );
        $type->groups[] = $group;
        $type->getState( 'properties' )->groupIds[] = $group->id;
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * @param Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\InvalidArgumentType If field has id already
     * @throws \ezp\Base\Exception\NotFound If type is not found
     */
    public function addFieldDefinition( Type $type, FieldDefinition $field  )
    {
        if ( $field->id )
            throw new InvalidArgumentType( '$field->id', 'false' );

        $this->handler->contentTypeHandler()->addFieldDefinition(
            $type->id,
            $type->status,
            $field->getState( "properties" )
        );
        $type->fields[] = $field;
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     */
    public function removeFieldDefinition( Type $type, FieldDefinition $field  )
    {
        $this->handler->contentTypeHandler()->removeFieldDefinition(
            $type->id,
            $type->status,
            $field->id
        );
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     */
    public function updateFieldDefinition( Type $type, FieldDefinition $field  )
    {
        $this->handler->contentTypeHandler()->updateFieldDefinition(
            $type->id,
            $type->status,
            $field->getState( "properties" )
        );
    }

    /**
     * @param \ezp\Persistence\Content\Type $vo
     * @return \ezp\Content\Type
     */
    protected function buildType( TypeValue $vo )
    {
        $type = new Type();
        foreach ( $vo->fieldDefinitions as $fieldDefinitionVo )
        {
            $fieldDefinition = new FieldDefinition( $type, $fieldDefinitionVo->fieldType );
            $type->fields[] = $fieldDefinition->setState( array( 'properties' => $fieldDefinitionVo ) );
        }
        $type->setState(
            array(
                "properties" => $vo,
                "groups" => new LazyIdList(
                    "ezp\\Content\\Type\\Group",
                    $vo->groupIds,
                    $this,
                    "loadGroup"
                )
            )
        );
        $this->storage->add( $type, array( 'id' => $vo->id, 'status' => $vo->status ) );
        return $type;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group $vo
     * @return \ezp\Content\Type\Group
     */
    protected function buildGroup( GroupValue $vo )
    {
        $group = new Group();
        $group->setState(
            array(
                "properties" => $vo,
                "types" => new Lazy(
                    "ezp\\Content\\Type",
                    $this,
                    $vo->id,
                    "loadByGroupId"
                )
            )
        );
        $this->storage->add( $group, array( 'id' => $vo->id ) );
        return $group;
    }
}
