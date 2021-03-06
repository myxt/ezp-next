<?php
/**
 * File containing the Media Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Media;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\PropertyNotFound;

/**
 * Value for Media field type
 *
 * @property string $filename The name of the file in the eZ publish var directory
 *                            (for example "44b963c9e8d1ffa80cbb08e84d576735.avi").
 * @property string $mimeType
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * BinaryFile object
     *
     * @var \ezp\Io\BinaryFile
     */
    public $file;

    /**
     * Original file name
     *
     * @var string
     */
    public $originalFilename;

    /**
     * The playback width - in number of pixels (for example "640").
     *
     * @var int
     */
    public $width = 0;

    /**
     * The playback height - in number of pixels (for example "480").
     *
     * @var int
     */
    public $height = 0;

    /**
     * Flag indicating to show controller or not
     *
     * @var bool
     */
    public $hasController = true;

    /**
     * Real Media specific - controls the control-bar
     *
     * @var bool
     */
    public $controls = false;

    /**
     * Automatically start playback or not
     *
     * @var bool
     */
    public $isAutoplay = true;

    /**
     * A URL that leads to the plug-in that is required for proper playback.
     *
     * @see \ezp\Content\FieldType\Media\Handler::getPluginspageByType
     * @var string
     */
    public $pluginspage;

    /**
     * Flash specific - controls the quality of the media.
     *
     * @var int
     */
    public $quality;

    /**
     * Flag indicating if media should be looped playback or single-cycle
     *
     * @var bool
     */
    public $isLoop = false;

    /**
     * @var \ezp\Content\FieldType\Media\Handler
     */
    protected $handler;

    /**
     * Construct a new Value object.
     * To affect a BinaryFile object to the $file property, use the handler:
     * <code>
     * use \ezp\Content\FieldType\Media;
     * $binaryValue = new BinaryFile\Value;
     * $binaryValue->file = $binaryValue->getHandler()->createFromLocalPath( '/path/to/local/file.txt' );
     * </code>
     *
     * @param null|string $file File to use
     */
    public function __construct( $file = null )
    {
        $this->handler = new Handler;

        if ( $file !== null )
        {
            $this->file = $this->handler->createFromLocalPath( $file );
            $this->originalFilename = basename( $file );
        }
    }

    /**
     * @see \ezp\Content\FieldType\Value
     * @return \ezp\Content\FieldType\Media\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return $this->file->path;
    }

    /**
     * Magic getter
     *
     * @param string $name
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'filename':
                return basename( $this->file->path );
                break;

            case 'mimeType':
                return $this->file->contentType->__toString();
                break;

            case 'mimeTypeCategory':
                return $this->file->contentType->type;
                break;

            case 'mimeTypePart':
                return $this->file->contentType->subType;
                break;

            case 'filesize':
                return $this->file->size;
                break;

            case 'filepath':
                return $this->file->path;
                break;

            default:
                throw new PropertyNotFound( $name, get_class( $this ) );
        }
    }

    /**
     * Returns handler object.
     * Useful manipulate {@link self::$file}
     *
     * @return \ezp\Content\FieldType\Media\Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        return $this->originalFilename;
    }
}
