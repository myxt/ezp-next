<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 *
 */
class ContentTypeHandlerTest extends HandlerTest
{
    /**
     * Test create group function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $group = $this->persistenceHandler->ContentTypeHandler()->createGroup( $this->getGroupCreateStruct() );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type\\Group', $group );
        $this->assertEquals( array( 'eng-GB' => 'Media' ), $group->name );
    }

    /**
     * Test update group function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $struct = new GroupUpdateStruct();
        $struct->id = 1;
        $struct->modified = time();
        $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Content2' );
        $struct->description = array( 'eng-GB' => 'TestTest' );
        $struct->identifier = 'content2';
        $this->persistenceHandler->ContentTypeHandler()->updateGroup( $struct );
        $group = $this->persistenceHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertEquals( 1, $group->id );
        $this->assertEquals( array( 'eng-GB' => 'Content2' ), $group->name );
    }

    /**
     * Test delete group function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->persistenceHandler->ContentTypeHandler()->deleteGroup( 1 );

        try
        {
            $this->persistenceHandler->ContentTypeHandler()->loadGroup( 1 );
            $this->fail( "Group not deleted correctly" );
        }
        catch ( NotFound $e )
        {
        }

        $this->assertEquals(
            array(),
            $this->persistenceHandler->ContentTypeHandler()->load( 1 )->groupIds
        );
    }

    /**
     * Test load group function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type\\Group', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $obj->name );
    }

    /**
     * Test load all groups function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $list = $this->persistenceHandler->ContentTypeHandler()->loadAllGroups();
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type\\Group', $list[0] );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $list[0]->name );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::loadContentTypes
     */
    public function testLoadByGroup()
    {
        $list = $this->persistenceHandler->ContentTypeHandler()->loadContentTypes( 1, 0 );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $list[0] );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( 'folder', $list[0]->identifier );

        $list = $this->persistenceHandler->ContentTypeHandler()->loadContentTypes( 22, 0 );
        $this->assertEquals( array(), $list );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::load
     */
    public function testLoad()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->load( 1, 0 );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'folder', $obj->identifier );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadUnExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->load( 22, 0 );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadUnExistingStatus()
    {
        $this->persistenceHandler->ContentTypeHandler()->load( 1, 1 );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct() );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( array(), $obj->fieldDefinitions );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::create
     */
    public function testCreateWithFieldDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $struct = $this->getTypeCreateStruct();
        $struct->fieldDefinitions[] = $field =$this->getTypeFieldDefinition();

        $obj = $handler->create( $struct );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $field->id = $obj->fieldDefinitions[0]->id;
        $this->assertEquals( array( $field ), $obj->fieldDefinitions );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::update
     */
    public function testUpdate()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->update( 1, 0, $this->getTypeUpdateStruct() );
        $obj = $handler->load( 1, 0 );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( 10, $obj->modifierId );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::delete
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDelete()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->delete( 1, 0 );
        $this->assertNull( $handler->load( 1, 0 ) );
    }

    /**
     * Test createDraft function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::createDraft
     */
    public function testCreateDraft()
    {
        $userId = 10;
        $time = time();
        $obj = $this->persistenceHandler->ContentTypeHandler()->createDraft( $userId, 1 );
        $original = $this->persistenceHandler->ContentTypeHandler()->load( 1, Type::STATUS_DEFINED );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( $original->creatorId, $obj->creatorId );
        $this->assertEquals( $original->created, $obj->created );//ehm
        $this->assertEquals( $userId, $obj->modifierId );
        $this->assertGreaterThanOrEqual( $time, $obj->modified );//ehm
        $this->assertEquals( Type::STATUS_DRAFT, $obj->status );
        $this->assertEquals( 3, count( $obj->fieldDefinitions ) );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test createDraft function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::createDraft
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCreateDraftNonExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->createDraft( 10, 999 );
    }

    /**
     * Test createDraft function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::createDraft
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCreateDraftNonExistingDefinedType()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct( Type::STATUS_DRAFT ) );
        $obj2 = $handler->createDraft( 10, $obj->id );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::copy
     */
    public function testCopy()
    {
        $userId = 10;
        $time = time();
        $obj = $this->persistenceHandler->ContentTypeHandler()->copy( $userId, 1, Type::STATUS_DEFINED );
        $original = $this->persistenceHandler->ContentTypeHandler()->load( 1, Type::STATUS_DEFINED );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type', $obj );
        $this->assertStringStartsWith( 'folder_', $obj->identifier );
        $this->assertEquals( $userId, $obj->creatorId );
        $this->assertEquals( $userId, $obj->modifierId );
        $this->assertGreaterThanOrEqual( $time, $obj->created );
        $this->assertGreaterThanOrEqual( $time, $obj->modified );
        $this->assertEquals( Type::STATUS_DRAFT, $obj->status );
        $this->assertGreaterThan( $original->created, $obj->created );
        $this->assertEquals( 3, count( $obj->fieldDefinitions ) );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyNonExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->copy( 10, 22, 0 );
    }

    /**
     * Test copy function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyNonExistingStatus()
    {
        $this->persistenceHandler->ContentTypeHandler()->copy( 10, 1, 1 );
    }

    /**
     * Test link function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::link
     */
    public function testLink()
    {
        $group = $this->getGroupCreateStruct();
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $vo = $handler->createGroup( $group );
        $handler->link( $vo->id, 1, 0 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( array( 1, $vo->id ), $type->groupIds );
    }

    /**
     * Test link function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkMissingGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 64, 1, 0 );
    }

    /**
     * Test link function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkMissingType()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 1, 64, 0 );
    }

    /**
     * Test link function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::link
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testLinkExistingGroupLink()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 1, 1, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::unlink
     */
    public function testUnLink()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $group = $this->getGroupCreateStruct();
        $vo = $handler->createGroup( $group );
        $handler->link( $vo->id, 1, 0 );
        $handler->unlink( 1, 1, 0 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( array( $vo->id ), $type->groupIds );
    }

    /**
     * Test unlink function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::unlink
     * @expectedException \ezp\Base\Exception\NotFound
     * @todo Will have to insert broken data to be able to test this (a group type is part of that does not exist)
     */
    /*public function testUnLinkMissingGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 64, 1, 0 );
    }*/

    /**
     * Test unlink function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::unlink
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUnLinkMissingType()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 1, 64, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkNotInGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 2, 1, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkLastGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 1, 1, 0 );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $vo = $handler->addFieldDefinition( 1, 0, $field );
        $this->assertInstanceOf( 'ezp\\Persistence\\Content\\Type\\FieldDefinition', $vo );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 4, count( $type->fieldDefinitions ) );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::addFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAddFieldDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $handler->addFieldDefinition( 22, 0, $field );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::addFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAddFieldDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $handler->addFieldDefinition( 1, 1, $field );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinitionDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 0, 1 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 2, count( $type->fieldDefinitions ) );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 22, 0, 1 );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 1, 1 );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionInvalidFieldId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 0, 22 );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinitionDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 0, $fieldDefinition );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 3, count( $type->fieldDefinitions ) );
        $this->assertEquals( array( 'eng-GB' => 'Name', 'nor-NB' => 'Navn' ), $type->fieldDefinitions[0]->name );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 22, 0, $fieldDefinition );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 1, $fieldDefinition );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionDefinitionInvalidFieldDefinitionId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->id = 22;
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 0, $fieldDefinition );
    }

    /**
     * Test publish function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->copy( 10, 1, Type::STATUS_DEFINED );
        $handler->publish( $type->id );
        try {
            $handler->load( $type->id, Type::STATUS_DRAFT );
            $this->fail( "Draft of Type still exists after publish()" );
        }
        catch ( \Exception $e ){}
        $type = $handler->load( $type->id, Type::STATUS_DEFINED );

        $this->assertEquals( 10, $type->creatorId );
        $this->assertEquals( 10, $type->modifierId );
        $this->assertEquals( array( 'eng-GB' => 'Folder' ), $type->name );
        $this->assertEquals( 3, count( $type->fieldDefinitions ) );
        $this->assertEquals( array( 'eng-GB' => 'Name' ), $type->fieldDefinitions[0]->name );

        $org = $handler->load( 1, Type::STATUS_DEFINED );
        $this->assertNotEquals( $org->id, $type->id );
        $this->assertNotEquals( $org->remoteId, $type->remoteId );
        $this->assertNotEquals( $org->identifier, $type->identifier );
        $this->assertGreaterThan( $org->created, $type->created );
        $this->assertGreaterThan( $org->modified, $type->modified );
    }

    /**
     * Test publish function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::publish
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testPublishInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->publish( 999 );
    }

    /**
     * Test publish function
     *
     * @covers ezp\Persistence\Storage\InMemory\ContentTypeHandler::publish
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testPublishNoDraft()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->publish( 1 );
    }

    /**
     * @return \ezp\Persistence\Content\Type\CreateStruct
     */
    private function getTypeCreateStruct( $status = Type::STATUS_DEFINED )
    {
        $struct = new CreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->status = $status;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        $struct->fieldDefinitions = array();
        $struct->groupIds = array( 1 );
        return $struct;
    }

    /**
     * @return \ezp\Persistence\Content\Type\UpdateStruct
     */
    protected function getTypeUpdateStruct()
    {
        $struct = new UpdateStruct();
        $struct->modified = time();
        $struct->modifierId = 10;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        return $struct;
    }

    /**
     * @return \ezp\Persistence\Content\Type\Group\CreateStruct
     */
    protected function getGroupCreateStruct()
    {
        $struct = new GroupCreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Media' );
        $struct->description = array( 'eng-GB' => 'Group for media content types' );
        $struct->identifier = 'media';
        return $struct;
    }

    /**
     * @return \ezp\Persistence\Content\Type\FieldDefinition
     */
    protected function getTypeFieldDefinition()
    {
        $field =  new FieldDefinition();
        $field->identifier = 'title';
        $field->fieldType = 'ezstring';
        $field->position = 0;
        $field->isTranslatable = $field->isRequired = true;
        $field->isInfoCollector = false;
        $field->defaultValue = 'New Article';
        $field->name = array( 'eng-GB' => "Title" );
        $field->description = array( 'eng-GB' => "Title, used for headers, and url if short_title is empty" );
        return $field;
    }
}
