<?php

namespace ElementaryFramework\Annotations\Test\Sample;

use ElementaryFramework\Annotations\Annotation;

/**
 * @usage('class'=>true)
 */
class SampleAnnotation extends Annotation
{
    public $test = 'ok';
}

class DefaultSampleAnnotation extends SampleAnnotation
{

}
