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
    ezp\Base\ModelStorageInterface,
    SplObjectStorage;

/**
 * Model Storage, object storage for models with dirty tracking
 *
 */
class ModelStorage extends SplObjectStorage implements Observer, ModelStorageInterface
{
    /**
     * Attach a model object
     *
     * @param \ezp\Base\Model $object
     * @param array $data
     * @return ModelStorage
     */
    public function add( Model $object, array $data = array() )
    {
        $object->attach( $this );
        parent::attach( $object, $data + array( 'is_dirty' => false ) );
        return $this;
    }

    /**
     * Checks if storage contains a model
     *
     * @param \ezp\Base\Model $object
     * @return bool
     */
     public function has( Model $object )
     {
         return parent::contains( $object );
     }

    /**
     * Detach a model object
     *
     * @param \ezp\Base\Model $object
     * @return ModelStorage
     */
    public function remove( Model $object )
    {
        $object->detach( $this );
        parent::detach( $object );
        return $this;
    }

    /**
     * Get hash value for a given Model object
     *
     * @param \ezp\Base\Model $object
     * @return array
     */
     public function get( Model $object )
     {
         return $this->offsetGet( $object );
     }

    /**
     * Set hash value for a given Model object
     *
     * @param \ezp\Base\Model $object
     * @param array $data
     * @return ModelStorage
     */
     public function set( Model $object, array $data = array() )
     {
         $this->offsetSet( $object, $data );
         return $this;
     }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\Base\Model $subject
     * @param string $event
     * @return ModelStorage
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $event !== 'update' )
            return $this;

        // Re attach object if it has been purged from object cache for freeing memory
        // @todo impl purge function that only removes from storage but not detaches observer
        // @todo impl some sort of proper dirty tracking instead of assuming it is dirty on update
        if ( !parent::contains( $subject ) )
            parent::attach( $subject, array( 'is_dirty' => true ) );
        else
            $this[$subject] = array( 'is_dirty' => true ) + $this[$subject];
        return $this;
    }

    /**
     * Purge clean objects cache or all if $dirty is true
     *
     * @param bool $dirty
     * @param bool $detachObserver
     * @return void
     */
     public function purge( $dirty = false, $detachObserver = false )
     {
         foreach ( $this as $object => $data )
         {
             if ( !$dirty && !$data['is_dirty'] )
                 continue;

             if ( $detachObserver )
                 $this->remove( $object );
             else
                 parent::detach( $object );
         }
     }
}
