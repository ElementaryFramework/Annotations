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
 * This Annotation is mandatory, and must be applied to all Annotations.
 */
class UsageAnnotation extends Annotation
{
    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to classes.
     */
    public $class = false;

    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to properties.
     */
    public $property = false;

    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to methods.
     */
    public $method = false;

    /**
     * @var boolean $multiple Set this to TRUE for Annotations that allow multiple instances on the same member.
     */
    public $multiple = false;

    /**
     * @var boolean $inherited Set this to TRUE for Annotations that apply to members of child classes.
     */
    public $inherited = false;
}
