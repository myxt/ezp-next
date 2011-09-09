<?php
/**
 * File containing ModelStorage class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Exception\Logic,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\Model,
    ezp\Base\ModelStorageInterface;

/**
 * Model Storage, object storage for models with simple dirty tracking
 *
 */
class ModelStorage implements Observer, ModelStorageInterface
{
    /**
     * The identity map that holds references to all managed entities.
     *
     * The entities are grouped by their class name and then primary string, which is a
     * imploded string of primary id's. Together with object, update state is kept for internal use.
     * Structure Allows object to be purged if storage reaches a certain size removing references on
     * clean objects while still being able to identify object as being persisted if it is still used in BL.
     *
     * Structure:
     *  array(
     *      'ezp\Content\Type' => array(
     *          '1_0' => array(
     *              'object' => Model|null,
     *              'updated' => bool,
     *              'primaryIds' => array( 'id' => 1, 'status' => 0 ),
     *          )
     *      )
     *  )
     *
     * @var array[]Model
     */
    private $identityMap = array();

    /**
     * Map of all identifiers of managed entities
     *
     * Keys are object hash (spl_object_hash) and value is primary key string {@see $identityMap}
     * As code that uses this have an instance of the object, class name can be retrieved from it.
     *
     * Structure:
     *  array(
     *      '<object_hash>' => '1_0'
     *  )
     *
     * @var array[]
     */
    private $entityIdentifiers = array();

    /**
     * Attach a model object
     *
     * @param \ezp\Base\Model $object
     * @param array $primaryIds Eg: array( 'id' => 2 ) or array( 'id' => 2, 'status' => 1 )
     * @return bool False if object already was part of storage
     * @throws \ezp\Base\Exception\Logic If object is already persisted but not by same object hash
     */
    public function add( Model $object, array $primaryIds )
    {
        $hash = spl_object_hash( $object );
        if ( isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        $primaryIdString = implode( '_', $primaryIds );
        if ( isset( $this->identityMap[$className][$primaryIdString] ) )
            throw new Logic( 'ModelStorage->add()', 'primaryIdString of Model is already persisted but not with same object_hash' );

        $object->attach( $this, 'update' );
        $object->attach( $this, 'destruct' );
        $this->identityMap[$className][$primaryIdString] = array(
            'object' => $object,
            'updated' => false,
            'primaryIds' => $primaryIds,
        );
        $this->entityIdentifiers[$hash] = $primaryIdString;
        return true;
    }

    /**
     * Checks if storage contains a model
     *
     * @param \ezp\Base\Model $object
     * @return bool|null Null means the object is not in array but object is managed
     *                   aka object has been removed from array for memory preserving reasons,
     *                   but object is still managed.
     * @throws \ezp\Base\Exception\Logic If primaryIdString found by object hash was not found
     */
    public function has( Model $object )
    {
        $hash = spl_object_hash( $object );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        $primaryIdString = $this->entityIdentifiers[$hash];
        if ( !isset( $this->identityMap[$className][$primaryIdString] ) )
            throw new Logic( 'ModelStorage->has()', 'primaryIdString of $object by object hash lookup is not persisted' );

        if ( !isset( $this->identityMap[$className][$primaryIdString]['object'] ) )
            return null;

        return true;
    }

    /**
     * Detach a model object
     *
     * @param \ezp\Base\Model $object
     * @return bool
     */
    public function remove( Model $object )
    {
        $hash = spl_object_hash( $object );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            throw new InvalidArgumentValue( 'ModelStorage->remove(): $object', $object );

        $className = get_class( $object );
        $primaryIdString = $this->entityIdentifiers[$hash];
        unset( $this->entityIdentifiers[$hash] );

        $object->detach( $this, 'update' );
        $object->detach( $this, 'destruct' );

        if ( !isset( $this->identityMap[$className][$primaryIdString] ) )
            throw new Logic( 'ModelStorage->remove()', 'primaryIdString of $object by object hash lookup is not persisted' );

        unset( $this->identityMap[$className][$primaryIdString] );
        return true;
    }

    /**
     * Get a Model object by class name and primary id
     *
     * @param string $className
     * @param array $primaryIds Eg: array( 'id' => 2 ) or array( 'id' => 2, 'status' => 1 )
     * @return \ezp\Base\Model|null
     */
    public function get( $className, array $primaryIds )
    {
        $primaryIdString = implode( '_', $primaryIds );
        if ( !isset( $this->identityMap[$className][$primaryIdString]['object'] ) )
            return null;
        return $this->identityMap[$className][$primaryIdString]['object'];
    }

    /**
     * Called when subject has been updated
     *
     * @internal Observable is used to listen for events on object in storage
     * @param \ezp\Base\Observable $subject
     * @param string $event
     * @param array|null $arguments
     * @return \ezp\Base\Observer
     * @throws \ezp\Base\Exception\InvalidArgumentValue If object is not found by object_hash
     * @throws \ezp\Base\Exception\Logic If primaryIdString found by object hash was not found
     */
    public function update( Observable $subject, $event = 'update', array $arguments = null )
    {
        if ( $event === 'destruct' )
            $this->remove( $subject );

        if ( $event !== 'update' )
            return $this;

        $hash = spl_object_hash( $subject );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            throw new InvalidArgumentValue( 'ModelStorage->update(): $subject', $subject );

        $className = get_class( $subject );
        $primaryIdString = $this->entityIdentifiers[$hash];
        if ( !isset( $this->identityMap[$className][$primaryIdString] ) )
            throw new Logic( 'ModelStorage->update()', 'primaryIdString of $subject by object hash lookup is not persisted' );

        if ( !isset( $this->identityMap[$className][$primaryIdString]['object'] ) )
            $this->identityMap[$className][$primaryIdString]['object'] = $subject;

        $this->identityMap[$className][$primaryIdString]['updated'] = true;
        return $this;
    }
}
