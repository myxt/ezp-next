<?php
/**
 * File containing ModelStorage interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Model,
    ezp\Base\Collection;

/**
 * Interface for Model Storage, object storage for models with dirty tracking
 *
 */
interface ModelStorageInterface
{
    /**
     * Attach a model object
     *
     * @param \ezp\Base\Model $object
     * @param array $data
     * @return ModelStorageInterface
     */
    public function add( Model $object, array $data = array() );

    /**
     * Checks if storage contains a model
     *
     * @param \ezp\Base\Model $object
     * @return bool
     */
     public function has( Model $object );

    /**
     * Detach a model object
     *
     * @param \ezp\Base\Model $object
     * @return ModelStorageInterface
     */
    public function remove( Model $object );

    /**
     * Get hash value for a given Model object
     *
     * @param \ezp\Base\Model $object
     * @return array
     */
     public function get( Model $object );

    /**
     * Set hash value for a given Model object
     *
     * @param \ezp\Base\Model $object
     * @param array $data
     * @return ModelStorageInterface
     */
     public function set( Model $object, array $data = array() );

    /**
     * Purge clean objects cache or all if $dirty is true
     *
     * @param bool $dirty
     * @param bool $detachObserver
     * @return void
     */
     public function purge( $dirty = false, $detachObserver = false );
}
