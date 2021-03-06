<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Location\Gateway\EzpDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Location\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Storage\Legacy\Content\Location\Gateway\EzcDatabase,
    ezp\Persistence;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Location\Gateway\EzcDatabase
 */
class EzpDatabaseTest extends TestCase
{
    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    protected function getLocationGateway()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new EzcDatabase( $dbHandler );
    }

    public static function getLoadLocationValues()
    {
        return array(
            array( 'node_id', 77 ),
            array( 'priority', 0 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'remote_id', 'dbc2f3c8716c12f32c379dbf0b1cb133' ),
            array( 'contentobject_id', 75 ),
            array( 'parent_node_id', 2 ),
            array( 'path_identification_string', 'solutions' ),
            array( 'path_string', '/1/2/77/' ),
            array( 'modified_subnode', 1311065017 ),
            array( 'main_node_id', 77 ),
            array( 'depth', 2 ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @dataProvider getLoadLocationValues
     */
    public function testLoadLocation( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeData( 77 );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadInvalidLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeData( 1337 );
    }

    public function testMoveSubtreePathUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->moveSubtreeNodes( '/1/2/69/', '/1/2/77/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 65, '/1/2/', 1 ),
                array( 67, '/1/2/77/69/', 3 ),
                array( 69, '/1/2/77/69/70/71/', 5 ),
                array( 73, '/1/2/77/69/72/75/', 5 ),
                array( 75, '/1/2/77/', 2 ),
            ),
            $query
                ->select( 'contentobject_id', 'path_string', 'depth' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 69, 71, 75, 77, 2 ) ) )
                ->orderBy( 'contentobject_id' )
        );
    }

    public function testMoveSubtreeAssignmentUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->updateNodeAssignment( 67, 2, 77, 5 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 67, 1, 0, 53, 1, 5, 77, '9cec85d730eec7578190ee95ce5a36f5', 0, 2, 1 ),
            ),
            $query
                ->select( '*' )
                ->from( 'eznode_assignment' )
                ->where( $query->expr->eq( 'contentobject_id', 67 ) )
        );
    }

    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $time = time();
        $handler->updateSubtreeModificationTime( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/69/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
                ->orderBy( 'path_string' )
        );
    }

    public function testHideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 1, 1 ),
                array( 75, 0, 1 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 75 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->unhideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 0, 0 ),
                array( 75, 0, 0 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 75 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideParentTree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->hideSubtree( '/1/2/69/70/' );
        $handler->unhideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 0, 0 ),
                array( 70, 1, 1 ),
                array( 71, 0, 1 ),
                array( 75, 0, 0 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70, 71, 75 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhidePartialSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->hideSubtree( '/1/2/69/70/' );
        $handler->unhideSubtree( '/1/2/69/70/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 1, 1 ),
                array( 70, 0, 1 ),
                array( 71, 0, 1 ),
                array( 75, 0, 1 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70, 71, 75 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    public function testSwapLocations()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->swap( 70, 78 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 70, 76 ),
                array( 78, 68 ),
            ),
            $query
                ->select( 'node_id', 'contentobject_id' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 70, 78 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    public function testCreateLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'remoteId' => 'some_id',
                )
            ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 70, '/1/2/69/70/' ),
                array( 77, '/1/2/77/' ),
                array( 228, '/1/2/77/228/' ),
            ),
            $query
                ->select( 'node_id', 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 68, 75 ) ) )
                ->orderBy( 'node_id' )
        );
    }

    public static function getCreateLocationValues()
    {
        return array(
            array( 'contentobject_id', 68 ),
            array( 'contentobject_is_published', 0 ),
            array( 'contentobject_version', 1 ),
            array( 'depth', 3 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'main_node_id', 42 ),
            array( 'parent_node_id', 77 ),
            array( 'path_identification_string', '' ),
            array( 'priority', 1 ),
            array( 'remote_id', 'some_id' ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getCreateLocationValues
     */
    public function testCreateLocationValues( $field, $value )
    {
        if ( $value === null )
        {
            $this->markTestIncomplete( 'Proper value setting yet unknown.' );
        }

        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 42,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                )
            ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->eq( 'node_id', 228 ) )
        );
    }

    public static function getCreateLocationReturnValues()
    {
        return array(
            array( 'id', 228 ),
            array( 'priority', 1 ),
            array( 'hidden', false ),
            array( 'invisible', false ),
            array( 'remoteId', 'some_id' ),
            array( 'contentId', '68' ),
            array( 'parentId', '77' ),
            array( 'pathIdentificationString', '' ),
            array( 'pathString', '' ),
            array( 'mainLocationId', 228 ),
            array( 'depth', 3 ),
            array( 'sortField', 1 ),
            array( 'sortOrder', 1 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getCreateLocationReturnValues
     */
    public function testCreateLocationReturnValues( $field, $value )
    {
        if ( $value === null )
        {
            $this->markTestIncomplete( 'Proper value setting yet unknown.' );
        }

        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $location = $handler->create(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                )
            ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $this->assertTrue( $location instanceof Location );
        $this->assertEquals( $value, $location->$field );
    }

    public static function getUpdateLocationData()
    {
        return array(
            array( 'priority', 23 ),
            array( 'remote_id', 'someNewHash' ),
            array( 'sort_field', 4 ),
            array( 'sort_order', 4 ),
        );
    }

    /**
     * @dataProvider getUpdateLocationData
     */
    public function testUpdateLocation( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->update(
            new Location\UpdateStruct(
                array(
                    'priority' => 23,
                    'remoteId' => 'someNewHash',
                    'sortField' => 4,
                    'sortOrder' => 4,
                )
            ),
            70
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 70 ) ) )
        );
    }

    public static function getNodeAssignmentValues()
    {
        return array(
            array( 'contentobject_version', 1 ),
            array( 'from_node_id', 0 ),
            array( 'id', 214 ),
            array( 'is_main', 0 ),
            array( 'op_code', 3 ),
            array( 'parent_node', 77 ),
            array( 'parent_remote_id', '' ),
            array( 'remote_id', 0 ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 0 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getNodeAssignmentValues
     */
    public function testCreateLocationNodeAssignmentCreation( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 1,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                )
            ),
            '77',
            EzcDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'eznode_assignment' )
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq( 'contentobject_id', 68 ),
                        $query->expr->eq( 'parent_node', 77 )
                    )
                )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignments()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 1,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                )
            ),
            '77',
            EzcDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments( 68, 1 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( '/1/2/77/228/' ) ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->lAnd(
                    $query->expr->eq( 'contentobject_id', 68 ),
                    $query->expr->eq( 'parent_node_id', 77 )
                ) )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsUpdateAssignment()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                array(
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 1,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                )
            ),
            '77',
            EzcDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments( 68, 1 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( EzcDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP ) ),
            $query
                ->select( 'op_code' )
                ->from( 'eznode_assignment' )
                ->where( $query->expr->lAnd(
                    $query->expr->eq( 'contentobject_id', 68 ),
                    $query->expr->eq( 'parent_node', 77 )
                ) )
        );
    }

    public function testSetSectionForSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../_fixtures/contentobjects.php' );
        $handler = $this->getLocationGateway();
        $handler->setSectionForSubtree( '/1/2/69/70/', 23 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( 68 ), array( 69 ) ),
            $query
                ->select( 'id' )
                ->from( 'ezcontentobject' )
                ->where( $query->expr->eq( 'section_id', 23 ) )
        );
    }
}

