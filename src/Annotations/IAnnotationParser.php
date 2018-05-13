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
 * This interface enables an Annotation to support PHP-DOC style Annotation
 * syntax - because this syntax is informal and varies between tags, such an
 * Annotation must be parsed by the individual Annotation class.
 */
interface IAnnotationParser
{
    /**
     * @param string $value The raw string value of the Annotation.
     *
     * @return array An array of Annotation properties.
     */
    public static function parseAnnotation($value);
}
