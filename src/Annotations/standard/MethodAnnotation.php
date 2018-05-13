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

use ElementaryFramework\Annotations\Annotation;
use ElementaryFramework\Annotations\AnnotationException;
use ElementaryFramework\Annotations\AnnotationFile;
use ElementaryFramework\Annotations\IAnnotationFileAware;
use ElementaryFramework\Annotations\IAnnotationParser;

/**
 * Defines a magic/virtual method
 *
 * @usage('class' => true, 'inherited' => true, 'multiple' => true)
 */
class MethodAnnotation extends Annotation implements IAnnotationParser, IAnnotationFileAware
{
    /**
     * Specifies the method return type.
     *
     * @var string
     */
    public $type;

    /**
     * Specifies the method name.
     *
     * @var string
     */
    public $name;

    /**
     * Specifies the parameters of the method.
     *
     * @var array
     */
    public $parameters;

    /**
     * Specifies the method description.
     *
     * @var string
     */
    public $description;

    /**
     * Annotation file.
     *
     * @var AnnotationFile
     */
    protected $file;

    /**
     * Parse the standard PHP-DOC "method" annotation.
     *
     * @param string $value The raw string value of the Annotation.
     *
     * @return array ['type', 'name', 'parameters'] or ['type', 'name', 'parameters', 'description'] if description is set.
     */
    public static function parseAnnotation(string $value): array
    {
        $matches = array();
        preg_match("/(\w+) (\w+)\(([^()]*)\) *(.*)/", trim($value), $matches);

        if (\count($matches) < 3) {
            // Malformed value, let "initAnnotation" report about it.
            return array();
        }

        $result = array(
            'type' => $matches[1],
            'name' => $matches[2],
            'parameters' => array()
        );

        if (isset($matches[3])) {
            if (\strlen($str = trim($matches[3])) > 0) {
                $params = \explode(",", $str);
                foreach ($params as $param) {
                    $parts = \explode(" ", trim($param));
                    if (\count($parts) > 1) {
                        $result['parameters'][] = array('type' => $parts[0], 'name' => \substr($parts[1], 1));
                    } else {
                        $result['parameters'][] = array('type' => 'mixed', 'name' => \substr($parts[0], 1));
                    }
                }
            }
        }

        if (isset($matches[4])) {
            $result['description'] = $matches[4];
        }

        return $result;
    }

    /**
     * Initialize the annotation.
     *
     * @param array $properties The array of annotation properties.
     *
     * @throws AnnotationException When the type or the name of the property is missing.
     */
    public function initAnnotation(array $properties)
    {
        $this->map($properties, array('type', 'name', 'parameters', 'description'));

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
