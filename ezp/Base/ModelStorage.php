<?php
/**
 * File containing ModelStorage class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\Model,
    ezp\Base\ModelStorageInterface;

/**
 * Model Storage, object storage for models with dirty tracking
 *
 */
class ModelStorage implements Observer, ModelStorageInterface
{
    /**
     * The identity map that holds references to all managed entities.
     *
     * The entities are grouped by their class name and then primary string, which is a
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
     * @var array[]
     */
    private $entityIdentifiers = array();

    /**
     * Attach a model object
     *
     * @param \ezp\Base\Model $object
     * @param array $primaryIds Eg: array( 'id' => 2 ) or array( 'id' => 2, 'version' => 1 )
     * @return bool False if object already was part of storage
     */
    public function add( Model $object, array $primaryIds )
    {
        $hash = spl_object_hash( $object );
        if ( isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        $primaryIdString = implode( '_', $primaryIds );
        if ( isset( $this->identityMap[$className][$primaryIdString] ) )
            return false;// @todo: throw runtime exception

        $object->attach( $this );
        $this->identityMap[$className][$primaryIdString] = $object;
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
     */
    public function has( Model $object )
    {
        $hash = spl_object_hash( $object );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        $primaryIdString = $this->entityIdentifiers[$hash];
        if ( !isset( $this->identityMap[$className][$primaryIdString] ) )
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
            return null;

        $className = get_class( $object );
        $primaryIdString = $this->entityIdentifiers[$hash];
        unset( $this->entityIdentifiers[$hash] );
        $object->detach( $this );

        if ( isset( $this->identityMap[$className][$primaryIdString] ) )
            unset( $this->identityMap[$className][$primaryIdString] );

        return true;
    }

    /**
     * Get a Model object by class name and primary id
     *
     * @param string $className
     * @param array $primaryIds Eg: array( 'id' => 2 ) or array( 'id' => 2, 'version' => 1 )
     * @return \ezp\Base\Model|null
     */
    public function get( $className, array $primaryIds )
    {
        $primaryIdString = implode( '_', $primaryIds );
        if ( !isset( $this->identityMap[$className][$primaryIdString] ) )
            return null;
        return $this->identityMap[$className][$primaryIdString];
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\Base\Observable $subject
     * @param string $event
     * @return Observer
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $event !== 'update' )
            return $this;

        // @todo Re attach object if it has been purged from object cache for freeing memory
        return $this;
    }
}
