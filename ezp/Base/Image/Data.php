<?php
/**
 * File containing the ImageData class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Image;
use ezcImageAnalyzerData,
    ezp\Base\Exception\PropertyNotFound;

/**
 * Description of ImageData
 */
class Data extends ezcImageAnalyzerData
{
    /**
     * Various advanced data, depending on image type
     *
     * @var array
     */
    public $advancedData = array();
}
