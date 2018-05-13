<?php

/**
 * Annotations
 *
 * Allows the creation of custom annotations in PHP.
 *
 * @category  Library
 * @package   Annotations
 * @author    Axel Nana <ax.lnana@outlook.com>
 * @copyright 2011-2015 Rasmus Schultz <rasmus@mindplay.dk>, 2018 Aliens Group, Inc.
 * @license   LGPL <http://github.com/ElementaryFramework/Annotations/blob/master/LICENSE>
 * @version   0.0.1
 *
 *
 * This file was originally a part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace ElementaryFramework\Annotations\Standard;

use ElementaryFramework\Annotations\AnnotationFile;
use ElementaryFramework\Annotations\Exceptions\AnnotationException;
use ElementaryFramework\Annotations\IAnnotationFileAware;
use ElementaryFramework\Annotations\IAnnotationParser;
use ElementaryFramework\Annotations\Annotation;

/**
 * Defines a method-parameter's type
 *
 * @usage('method'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class ParamAnnotation extends Annotation implements IAnnotationParser, IAnnotationFileAware
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * Annotation file.
     *
     * @var AnnotationFile
     */
    protected $file;

    /**
     * Parse the standard PHP-DOC "param" annotation.
     *
     * @param string $value The value to parse.
     *
     * @return array ['type', 'name']
     */
    public static function parseAnnotation(string $value): array
    {
        $parts = \explode(' ', \trim($value), 3);

        if (\count($parts) < 2) {
            // Malformed value, let "initAnnotation" report about it.
            return array();
        }

        return array('type' => $parts[0], 'name' => \substr($parts[1], 1));
    }

    /**
     * Initialize the annotation.
     *
     * @param array $properties The array of annotation properties.
     *
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        $this->map($properties, array('type', 'name'));

        parent::initAnnotation($properties);

        if (!isset($this->type)) {
            throw new AnnotationException(self::class . ' requires a type property');
        }

        if (!isset($this->name)) {
            throw new AnnotationException(self::class . ' requires a name property');
        }

        $this->type = $this->file->resolveType($this->type);
    }

    /**
     * Provides information about file, that contains this annotation.
     *
     * @param AnnotationFile $file Annotation file.
     *
     * @return void
     */
    public function setAnnotationFile(AnnotationFile $file)
    {
        $this->file = $file;
    }
}
