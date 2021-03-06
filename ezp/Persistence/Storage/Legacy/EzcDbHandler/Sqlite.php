<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\EzcDbHandler;
use ezp\Persistence\Storage\Legacy\EzcDbHandler;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 */
class Sqlite extends EzcDbHandler
{
    /**
     * Get auto increment value
     *
     * Returns the value used for autoincrement tables. Usually this will just
     * be null. In case for sequence based RDBMS this method can return a
     * proper value for the given column.
     *
     * @param string $table
     * @param string $column
     * @return mixed
     */
    public function getAutoIncrementValue( $table, $column )
    {
        if ( ( $table === 'ezcontentobject_attribute' ) &&
             (  $column === 'id' ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            return "0";
        }

        return parent::getAutoIncrementValue( $table, $column );
    }
}

