<?php

/**
 * This file is part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * This software is licensed under the GNU LGPL license
 * for more information, please see:
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace ElementaryFramework\Annotations\Demo\Annotations;

/**
 * Specifies validation requiring a non-empty value.
 *
 * @usage('property'=>true, 'inherited'=>true)
 */
class RequiredAnnotation extends ValidationAnnotationBase
{
}
