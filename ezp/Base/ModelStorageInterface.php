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
     * @param array $primaryIds Eg: array( 'id' => 2 ) or array( 'id' => 2, 'version' => 1 )
     */
    public function add( Model $object, array $primaryIds );

    /**
     * Checks if storage contains a model
     *
     * @param \ezp\Base\Model $object
     * @return bool|null Null means the object is not in array but object is managed
     *                   aka object has been removed from array for memory preserving reasons,
     *                   but object is still managed.
     */
     public function has( Model $object );

    /**
     * Detach a model object
     *
     * @param \ezp\Base\Model $object
     * @return bool
     */
    public function remove( Model $object );

    /**
     * Get hash value for a given Model object
     *
     * @param string $className
     * @param array $primaryIds
     * @return \ezp\Base\Model|null
     */
     public function get( $className, array $primaryIds );
}
