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


use ElementaryFramework\Annotations\AnnotationManager;

abstract class Package
{
    public static function register(AnnotationManager $annotationManager)
    {
        $annotationManager->registerAnnotation('length', 'ElementaryFramework\Annotations\Demo\Annotations\LengthAnnotation');
        $annotationManager->registerAnnotation('required', 'ElementaryFramework\Annotations\Demo\Annotations\RequiredAnnotation');
        $annotationManager->registerAnnotation('text', 'ElementaryFramework\Annotations\Demo\Annotations\TextAnnotation');
        $annotationManager->registerAnnotation('range', 'ElementaryFramework\Annotations\Demo\Annotations\RangeAnnotation');
     }
}
