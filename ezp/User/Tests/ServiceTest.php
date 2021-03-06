<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Tests;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\User\Proxy as ProxyUser,
    ezp\User\Concrete as ConcreteUser,
    ezp\User\Role\Concrete as ConcreteRole,
    ezp\User\Group,
    ezp\User\Role,
    ezp\User\Policy,
    ezp\Base\Exception\NotFound,
    Exception;

/**
 * Test case for Location class
 *
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * Test service function for creating user
     *
     * @covers \ezp\User\Service::create
     * @group userService
     */
    public function testCreate()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $do = new ConcreteUser( 1 );
        $do->login = $do->passwordHash = 'test';
        $do->email = 'test@ez.no';
        $do->hashAlgorithm = 2;
        $do = $service->create( $do );
        self::assertInstanceOf( 'ezp\\User', $do );
        self::assertEquals( 1, $do->id );
        self::assertEquals( 'test', $do->login );
        self::assertEquals( 'test@ez.no', $do->email );
    }

    /**
     * Test service function for creating user
     *
     * @covers \ezp\User\Service::create
     * @expectedException \ezp\Base\Exception\Logic
     * @group userService
     */
    public function testCreateExistingId()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $do = new ConcreteUser( 14 );
        $do->login = $do->passwordHash = 'test';
        $do->email = 'test@ez.no';
        $do->hashAlgorithm = 2;
        $service->create( $do );
    }

    /**
     * Test service function for creating user
     *
     * @covers \ezp\User\Service::create
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     * @group userService
     */
    public function testCreateMissingId()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $do = new ConcreteUser();
        $do->login = $do->passwordHash = 'test';
        $do->email = 'test@ez.no';
        $do->hashAlgorithm = 2;
        $service->create( $do );
    }

    /**
     * Test service function for loading user
     *
     * @covers \ezp\User\Service::load
     * @group userService
     */
    public function testLoad()
    {
        $service = $this->repository->getUserService();
        $do = $service->load( 14 );
        self::assertInstanceOf( 'ezp\\User', $do );
        self::assertEquals( 14, $do->id );
        self::assertEquals( 'admin', $do->login );
        self::assertEquals( 'spam@ez.no', $do->email );
    }

    /**
     * Test service function for loading user
     *
     * @covers \ezp\User\Service::load
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getUserService();
        $service->load( 999 );
    }

    /**
     * Test service function for loading user by credentials
     *
     * @covers \ezp\User\Service::loadByCredentials
     * @group userService
     */
    public function testLoadByCredentials()
    {
        $service = $this->repository->getUserService();
        $do = $service->loadByCredentials( 'admin', 'publish' );
        self::assertInstanceOf( 'ezp\\User', $do );
        self::assertEquals( 14, $do->id );
        self::assertEquals( 'admin', $do->login );
        self::assertEquals( 'spam@ez.no', $do->email );
    }

    /**
     * Test service function for loading user by credentials
     *
     * @covers \ezp\User\Service::loadByCredentials
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadByCredentialsNotFound()
    {
        $service = $this->repository->getUserService();
        $service->loadByCredentials( 'kore', 'password' );
    }

    /**
     * Test service function for loading user by credentials
     *
     * @covers \ezp\User\Service::loadByCredentials
     * @expectedException \ezp\User\Exception\FailedLogin
     * @group userService
     */
    public function testLoadByCredentialsFailedLogin()
    {
        $service = $this->repository->getUserService();
        $service->loadByCredentials( 'admin', 'password' );
    }

    /**
     * Test service function for updating user
     *
     * @covers \ezp\User\Service::update
     * @group userService
     */
    public function testUpdate()
    {
        $service = $this->repository->getUserService();
        $do = $service->load( 14 );
        $do->login = 'test';
        $service->update( $do );
        $do = $service->load( 14 );
        self::assertEquals( 14, $do->id );
        self::assertEquals( 'test', $do->login );
        self::assertEquals( 'spam@ez.no', $do->email );
    }

    /**
     * Test service function for updating user
     *
     * @covers \ezp\User\Service::update
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     * @group userService
     */
    public function testUpdateMissingId()
    {
        $service = $this->repository->getUserService();
        $do = $service->load( 14 );
        $do->login = null;
        $service->update( $do );
    }

    /**
     * Test service function for deleting user
     *
     * @covers \ezp\User\Service::delete
     * @group userService
     */
    public function testDelete()
    {
        $service = $this->repository->getUserService();
        $user = $service->load( 14 );
        try
        {
            $service->delete( $user );
        }
        catch ( Exception $e )
        {
            self::assertTrue( false, "Did not except any exceptions here, got: " . $e );
        }

        try
        {
            $service->load( 14 );
            self::assertTrue( false, "Except an exception here, as the object is deleted" );
        }
        catch ( NotFound $e )
        {
            self::assertTrue( true );
        }
    }

    /**
     * Test service function for deleting user
     *
     * @covers \ezp\User\Service::delete
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testDeleteNotFound()
    {
        $service = $this->repository->getUserService();
        $service->delete( new ProxyUser( 999, $service ) );
    }

    /**
     * Test service function for creating group
     *
     * @covers \ezp\User\Service::createGroup
     * @group userService
     */
    public function testCreateGroup()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $parent = $service->loadGroup( 12 );

        $groupName = 'A new group of users';
        $groupDescription = 'A new user group for testing';
        $do = $service->createGroup( $parent, $groupName, $groupDescription );
        self::assertInstanceOf( 'ezp\\User\\Group', $do );
        self::assertEquals( $groupName, (string)$do->name );
        self::assertEquals( $groupDescription, (string)$do->description );

        $group = $do->getParent();
        self::assertInstanceOf( 'ezp\\User\\Group', $group );
        self::assertEquals( 12, $group->id );
    }

    /**
     * Test service function for loading group
     *
     * @covers \ezp\User\Service::loadGroup
     * @group userService
     */
    public function testLoadGroup()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $do = $service->loadGroup( 12 );
        self::assertInstanceOf( 'ezp\\User\\Group', $do );
        self::assertEquals( 12, $do->id );
        self::assertEquals( 'Administrator users', (string)$do->name );
        self::assertEquals( '', (string)$do->description );

        $group = $do->getParent();
        self::assertInstanceOf( 'ezp\\User\\Group', $group );
        self::assertEquals( 4, $group->id );
    }

    /**
     * Test service function for loading group
     *
     * @covers \ezp\User\Service::loadGroup
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadGroupNotFound()
    {
        $service = $this->repository->getUserService();
        $service->loadGroup( 999 );
    }

    /**
     * Test service function for loading group
     *
     * @covers \ezp\User\Service::loadGroup
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     * @group userService
     */
    public function testLoadGroupNotFoundWithType()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $service->loadGroup( 1 );
    }

    /**
     * Test service function for assigning group location
     *
     * @covers \ezp\User\Service::assignGroup
     * @group userService
     */
    public function testAssignGroup()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $adminGroup = $service->loadGroup( 12 );
        $anonymousUser = $service->load( 10 );

        $service->assignGroup( $adminGroup, $anonymousUser );
        self::assertEquals( 2, count( $anonymousUser->getGroups() ) );

        // @todo Test that policies increases
    }

    /**
     * Test service function for assigning group location
     *
     * @covers \ezp\User\Service::assignGroup
     * @expectedException \ezp\Base\Exception\Logic
     * @group userService
     */
    public function testAssignGroupAlreadyAssigned()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $adminGroup = $service->loadGroup( 12 );
        $anonymousUser = $service->load( 10 );

        $service->assignGroup( $adminGroup, $anonymousUser );
        self::assertEquals( 2, count( $anonymousUser->getGroups() ) );

        $service->assignGroup( $adminGroup, $anonymousUser );
    }

    /**
     * Test service function for un-assigning group
     *
     * @covers \ezp\User\Service::unAssignGroup
     * @group userService
     */
    public function testUnAssignGroup()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $adminGroup = $service->loadGroup( 12 );
        $anonymousUser = $service->load( 10 );

        $service->assignGroup( $adminGroup, $anonymousUser );
        self::assertEquals( 2, count( $anonymousUser->getGroups() ) );

        $service->unAssignGroup( $adminGroup, $anonymousUser );
        self::assertEquals( 1, count( $anonymousUser->getGroups() ) );
        // @todo Test that policies decrease
    }

    /**
     * Test service function for un-assigning group
     *
     * @covers \ezp\User\Service::unAssignGroup
     * @expectedException \ezp\Base\Exception\Logic
     * @group userService
     */
    public function testUnAssignGroupNotAssigned()
    {
        $service = $this->repository->getUserService();
        $adminGroup = $service->loadGroup( 12 );
        $anonymousUser = $service->load( 10 );

        $service->unAssignGroup( $adminGroup, $anonymousUser );
    }

    /**
     * Test service function for creating role
     *
     * @covers \ezp\User\Service::createRole
     * @group userService
     */
    public function testCreateRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $do = $service->createRole( $this->getRole() );
        self::assertEquals( 'test', $do->name );
        self::assertEquals( array(), $do->groupIds );
        self::assertEquals( 3, count( $do->policies ) );
        self::assertEquals( 'user', $do->policies[2]->module );
        self::assertEquals( '*', $do->policies[2]->function );
        self::assertEquals( '*', $do->policies[2]->limitations );
        self::assertEquals( $do->id, $do->policies[2]->roleId );
    }

    /**
     * Test service function for creating role
     *
     * @covers \ezp\User\Service::createRole
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testCreateRoleForbidden()
    {
        $service = $this->repository->getUserService();
        $service->createRole( $this->getRole() );
    }

    /**
     * Test service function for loading role
     *
     * @covers \ezp\User\Service::loadRole
     * @group userService
     */
    public function testLoadRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $do = $service->createRole( $this->getRole() );

        $role = $service->loadRole( $do->id );
        self::assertEquals( $do->id, $role->id );
        self::assertEquals( 'test', $do->name );
    }

    /**
     * Test service function for loading role
     *
     * @covers \ezp\User\Service::loadRole
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadRoleNotFound()
    {
        $service = $this->repository->getUserService();
        $service->loadRole( 999 );
    }

    /**
     * Test service function for loading roles
     *
     * @covers \ezp\User\Service::loadRolesByGroupId
     * @group userService
     */
    public function testLoadRolesByGroupId()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        self::assertEquals( array(), $service->loadRolesByGroupId( 4 ) );

        $group = $service->loadGroup( 4 );//Users

        $do = $service->createRole( $this->getRole() );
        $service->assignRole( $group, $do );

        self::assertEquals( array( 4 ), $do->groupIds );
        self::assertEquals( 1, count( $group->getRoles() ) );

        $roles = $service->loadRolesByGroupId( 4 );
        self::assertEquals( 1, count( $roles ) );
        self::assertEquals( $do->id, $roles[0]->id );
    }

    /**
     * Test service function for loading roles
     *
     * @covers \ezp\User\Service::loadRolesByGroupId
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadRolesByGroupIdNotFound()
    {
        $service = $this->repository->getUserService();
        self::assertEquals( array(), $service->loadRolesByGroupId( 999 ) );
    }

    /**
     * Test service function for loading roles
     *
     * @covers \ezp\User\Service::loadRolesByGroupId
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     * @group userService
     */
    public function testLoadRolesByGroupIdNotFoundWithType()
    {
        $service = $this->repository->getUserService();
        $roles = $service->loadRolesByGroupId( 14 );
        self::assertEquals( 0, count( $roles ) );
        self::assertEquals( array(), $roles );
    }

    /**
     * Test service function for updating role
     *
     * @covers \ezp\User\Service::updateRole
     * @group userService
     */
    public function testUpdateRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $do = $service->createRole( $this->getRole() );

        $do->name = 'updated';
        $service->updateRole( $do );

        $role = $service->loadRole( $do->id );
        self::assertEquals( $do->id, $role->id );
        self::assertEquals( 'updated', $do->name );
    }

    /**
     * Test service function for updating role
     *
     * @covers \ezp\User\Service::updateRole
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testUpdateRoleForbidden()
    {
        $service = $this->repository->getUserService();
        $anon = $this->repository->setUser( $service->load( 14 ) );
        try
        {
            $do = $service->createRole( $this->getRole() );
            $this->repository->setUser( $anon );
        }
        catch ( \Exception $e )
        {
            self::fail( "Did not except any exceptions here, got: " . $e );
        }

        $do->name = 'updated';
        $service->updateRole( $do );
    }

    /**
     * Test service function for deleting role
     *
     * @covers \ezp\User\Service::delete
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testDeleteRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        try
        {
            $do = $service->createRole( $this->getRole() );
            $service->deleteRole( $do );
        }
        catch ( Exception $e )
        {
            self::fail( "Did not except any exceptions here, got: " . $e );
        }

        $service->loadRole( $do->id );
    }

    /**
     * Test service function for deleting role
     *
     * @covers \ezp\User\Service::deleteRole
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testDeleteRoleNotFound()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $role = new ConcreteRole();
        $role->getState( 'properties' )->id = 999;
        $service->deleteRole( $role );
    }

    /**
     * Test service function for deleting role
     *
     * @covers \ezp\User\Service::deleteRole
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testDeleteRoleForbidden()
    {
        $service = $this->repository->getUserService();
        $role = $service->loadRole( 1 );
        $service->deleteRole( $role );
    }

    /**
     * Test service function for adding policy on a role
     *
     * @covers \ezp\User\Service::addPolicy
     * @group userService
     */
    public function testAddPolicy()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $do = $service->createRole( $this->getRole() );

        $policy = new Policy( $do );
        $policy->module = 'Foo';
        $policy->function = 'Bar';
        $policy->limitations = array( 'Limit' => array( 'Test' ) );

        $service->addPolicy( $do, $policy );
        self::assertEquals( 4, count( $do->policies ) );
        self::assertEquals( 'Foo', $do->policies[3]->module );
        self::assertEquals( 'Bar', $do->policies[3]->function );
        self::assertEquals( array( 'Limit' => array( 'Test' ) ), $do->policies[3]->limitations );
        self::assertEquals( $do->id, $do->policies[3]->roleId );

        $do = $service->loadRole( $do->id );
        self::assertEquals( 4, count( $do->policies ) );
        self::assertEquals( 'Foo', $do->policies[3]->module );
        self::assertEquals( 'Bar', $do->policies[3]->function );
        self::assertEquals( array( 'Limit' => array( 'Test' ) ), $do->policies[3]->limitations );
        self::assertEquals( $do->id, $do->policies[3]->roleId );
    }

    /**
     * Test service function for adding policy on a role
     *
     * @covers \ezp\User\Service::addPolicy
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testAddPolicyForbidden()
    {
        $service = $this->repository->getUserService();
        try
        {
            $anon = $this->repository->setUser( $service->load( 14 ) );
            $do = $service->createRole( $this->getRole() );
            $this->repository->setUser( $anon );
        }
        catch ( \Exception $e )
        {
            self::assertTrue( false, 'Did not except an exception here, got:' . $e->getMessage() );
        }
        $policy = new Policy( $do );
        $policy->module = 'Foo';
        $policy->function = 'Bar';
        $policy->limitations = array( 'Limit' => array( 'Test' ) );

        $service->addPolicy( $do, $policy );
    }

    /**
     * Test service function for removing policy on a role
     *
     * @covers \ezp\User\Service::removePolicy
     * @group userService
     */
    public function testRemovePolicy()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $do = $service->createRole( $this->getRole() );

        $service->removePolicy( $do, $do->policies[2] );
        self::assertEquals( 2, count( $do->policies ) );
        self::assertEquals( 'content', $do->policies[1]->module );
        self::assertEquals( 'read', $do->policies[1]->function );
        self::assertEquals( '*', $do->policies[1]->limitations );
        self::assertEquals( $do->id, $do->policies[1]->roleId );

        $do = $service->loadRole( $do->id );
        self::assertEquals( 2, count( $do->policies ) );
        self::assertEquals( 'content', $do->policies[1]->module );
        self::assertEquals( 'read', $do->policies[1]->function );
        self::assertEquals( '*', $do->policies[1]->limitations );
        self::assertEquals( $do->id, $do->policies[1]->roleId );
    }

    /**
     * Test service function for removing policy on a role
     *
     * @covers \ezp\User\Service::removePolicy
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testRemovePolicyForbidden()
    {
        $service = $this->repository->getUserService();
        try
        {
            $anon = $this->repository->setUser( $service->load( 14 ) );
            $do = $service->createRole( $this->getRole() );
            $this->repository->setUser( $anon );
        }
        catch ( \Exception $e )
        {
            self::assertTrue( false, 'Did not except an exception here, got:' . $e->getMessage() );
        }
        $service->removePolicy( $do, $do->policies[2] );
    }

    /**
     * Test service function for loading policies by user id
     *
     * @covers \ezp\User\Service::loadPoliciesByUserId
     * @group userService
     */
    public function testLoadPoliciesByUserId()
    {
        $service = $this->repository->getUserService();
        $policies = $service->loadPoliciesByUserId( 10 );
        self::assertEquals( 8, count( $policies ) );
        self::assertEquals( 'content', $policies[0]->module );
        self::assertEquals( 'pdf', $policies[0]->function );
        self::assertEquals( array( 'Section' => array( 1 ) ), $policies[0]->limitations );
    }

    /**
     * Test service function for loading policies by user id
     *
     * @covers \ezp\User\Service::loadPoliciesByUserId
     * @group userService
     */
    public function testLoadPoliciesByUserIdWithNewPolicies()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $this->clearRolesByGroup( $service->loadGroup( 42 ) );

        $policies = $service->loadPoliciesByUserId( 10 );
        self::assertEquals( 0, count( $policies ) );
        self::assertEquals( array(), $policies );

        $group = $service->loadGroup( 4 );//Users

        $do = $service->createRole( $this->getRole() );
        $service->assignRole( $group, $do );

        self::assertEquals( array( 4 ), $do->groupIds );
        self::assertEquals( 1, count( $group->getRoles() ) );

        $policies = $service->loadPoliciesByUserId( 10 );
        self::assertEquals( 3, count( $policies ) );
        self::assertEquals( $do->id, $policies[0]->roleId );
    }

    /**
     * Test service function for loading policies by user id
     *
     * @covers \ezp\User\Service::loadPoliciesByUserId
     * @expectedException \ezp\Base\Exception\NotFound
     * @group userService
     */
    public function testLoadPoliciesByUserIdNotFound()
    {
        $service = $this->repository->getUserService();
        $service->loadPoliciesByUserId( 999 );
    }

    /**
     * Test service function for loading policies by user id
     *
     * @covers \ezp\User\Service::loadPoliciesByUserId
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     * @group userService
     */
    public function testLoadPoliciesByUserIdNotFoundWithType()
    {
        $service = $this->repository->getUserService();
        $service->loadPoliciesByUserId( 42 );
    }

    /**
     * Test user has access to
     *
     * @covers \ezp\User::hasAccessTo
     * @group userService
     */
    public function testLoadPoliciesByUserIdHasAccessTo()
    {
        $user = $this->repository->getUserService()->load( 10 );
        $limitationArray = $user->hasAccessTo( 'user', 'login' );
        $this->assertInternalType( 'array', $limitationArray );

        /*// Optimized result
        $this->assertEquals( 1, count( $limitationArray ) );
        $this->assertEquals(
            array(
                 array(
                     'SiteAccess' => array(
                         2576532274,
                         2582995467,
                         2479546403,
                         3781580864,
                     )
                 )
            ),
            $limitationArray
        );*/

        $this->assertEquals( 4, count( $limitationArray ) );
        $this->assertEquals(
            array(
                array( 'SiteAccess' => array( 2576532274 ) ),
                array( 'SiteAccess' => array( 2582995467 ) ),
                array( 'SiteAccess' => array( 2479546403 ) ),
                array( 'SiteAccess' => array( 3781580864 ) ),
            ),
            $limitationArray
        );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::assignRole
     * @group userService
     */
    public function testAssignRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $group = $service->loadGroup( 12 );//Administrator users
        $groupCount = count( $group->getRoles() );

        $do = $service->createRole( $this->getRole() );
        $service->assignRole( $group, $do );

        self::assertEquals( array( 12 ), $do->groupIds );
        self::assertEquals( $groupCount + 1, count( $group->getRoles() ) );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::assignRole
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @group userService
     */
    public function testAssignRoleExistingRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $group = $service->loadGroup( 12 );//Administrator users

        $do = $service->createRole( $this->getRole() );
        try
        {
            $service->assignRole( $group, $do );
        }
        catch ( \Exception $e )
        {
            self::assertTrue( false, 'Did not except an exception here, got:' . $e->getMessage() );
        }
        $service->assignRole( $group, $do );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::assignRole
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testAssignRoleForbidden()
    {
        $service = $this->repository->getUserService();
        try
        {
            $anon = $this->repository->setUser( $service->load( 14 ) );
            $group = $service->loadGroup( 12 );//Administrator users
            $do = $service->createRole( $this->getRole() );
            $this->repository->setUser( $anon );
        }
        catch ( \Exception $e )
        {
            self::assertTrue( false, 'Did not except an exception here, got:' . $e->getMessage() );
        }
        $service->assignRole( $group, $do );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::unAssignRole
     * @group userService
     */
    public function testUnAssignRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $group = $service->loadGroup( 12 );//Administrator users
        $groupCount = count( $group->getRoles() );

        $do = $service->createRole( $this->getRole() );
        $service->assignRole( $group, $do );
        $service->unAssignRole( $group, $do );

        self::assertEquals( array(), $do->groupIds );
        self::assertEquals( $groupCount, count( $group->getRoles() ) );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::unAssignRole
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @group userService
     */
    public function testUnAssignRoleExistingRole()
    {
        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );
        $group = $service->loadGroup( 12 );//Administrator users

        $do = $service->createRole( $this->getRole() );
        $service->unAssignRole( $group, $do );
    }

    /**
     * Test service function for assigning role
     *
     * @covers \ezp\User\Service::unAssignRole
     * @expectedException \ezp\Base\Exception\Forbidden
     * @group userService
     */
    public function testUnAssignRoleForbidden()
    {
        $service = $this->repository->getUserService();
        try
        {
            $anon = $this->repository->setUser( $service->load( 14 ) );
            $group = $service->loadGroup( 12 );//Administrator users
            $do = $service->createRole( $this->getRole() );
            $service->assignRole( $group, $do );
            $this->repository->setUser( $anon );
        }
        catch ( \Exception $e )
        {
            self::assertTrue( false, 'Did not except an exception here, got:' . $e->getMessage() );
        }

        $service->unAssignRole( $group, $do );
    }

    /**
     * @return \ezp\User\Role
     */
    protected function getRole()
    {
        $do = new ConcreteRole();
        $do->name = 'test';

        $do->addPolicy( $policy = new Policy( $do ) );
        $policy->module = 'content';
        $policy->function = 'write';
        $policy->limitations = array( 'SubTree' => array( '/1/2/' ) );

        $do->addPolicy( $policy = new Policy( $do ) );
        $policy->module = 'content';
        $policy->function = 'read';
        $policy->limitations = '*';

        $do->addPolicy( $policy = new Policy( $do ) );
        $policy->module = 'user';
        $policy->function = $policy->limitations = '*';
        return $do;
    }

    /**
     * Clear all roles (and policies) assignments on a user group
     *
     * @param \ezp\User\Group $group
     */
    protected function clearRolesByGroup( Group $group )
    {
        $service = $this->repository->getUserService();
        $roles = $service->loadRolesByGroupId( $group->id );
        foreach ( $roles as $role )
        {
            $service->unAssignRole( $group, $role );
        }
    }
}
