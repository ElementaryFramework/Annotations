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

namespace ElementaryFramework\Annotations;

/**
 * A general-purpose base class for annotations.
 *
 * Annotations must implement IAnnotation, and may optionally derive
 * from this base class.
 */
abstract class Annotation implements IAnnotation
{
    /**
     * Insulation against read-access to undeclared properties
     */
    public function __get($name)
    {
        throw new AnnotationException(\get_class($this) . "::\${$name} is not a valid property name");
    }

    /**
     * Insulation against write-access to undeclared properties
     */
    public function __set($name, $value)
    {
        throw new AnnotationException(\get_class($this) . "::\${$name} is not a valid property name");
    }

    /**
     * Helper method - initializes unnamed properties.
     *
     * @param array &$properties Array of annotation properties, as passed into IAnnotation::initAnnotation()
     * @param array $indexes Array of unnamed properties
     */
    protected function map(array &$properties, array $indexes)
    {
        foreach ($indexes as $index => $name) {
            if (isset($properties[$index])) {
                $this->$name = $properties[$index];
                unset($properties[$index]);
            }
        }
    }

    /**
     * Initializes this annotation instance.
     * @see IAnnotation
     */
    public function initAnnotation(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }
}
