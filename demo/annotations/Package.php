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

namespace mindplay\demo\annotations;


use ElementaryFramework\Annotations\AnnotationManager;

abstract class Package
{
    public static function register(AnnotationManager $annotationManager)
    {
        $annotationManager->registerAnnotation('length', 'mindplay\demo\annotations\LengthAnnotation');
        $annotationManager->registerAnnotation('required', 'mindplay\demo\annotations\RequiredAnnotation');
        $annotationManager->registerAnnotation('text', 'mindplay\demo\annotations\TextAnnotation');
        $annotationManager->registerAnnotation('range', 'mindplay\demo\annotations\RangeAnnotation');
     }
}
