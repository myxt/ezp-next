<?php
/**
 * File contains: ezp\Content\Tests\Service\Legacy\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service\Legacy;
use ezp\Content\Tests\Service\SectionTest as InMemorySectionTest;

/**
 * Legacy test case for Section class
 *
 */
class SectionTest extends InMemorySectionTest
{

    protected static function getRepository()
    {
        return include 'common.php';
    }
}
