<?php

namespace ElementaryFramework\Annotations\Test\Sample;

class OrphanedAnnotations
{

    /**
     * Some method.
     *
     * @return void
     */
    public function someMethod()
    {
        $a = 5;

        // @codeCoverageIgnoreStart
        if (false) {
            $a = 6;
        }
        // @codeCoverageIgnoreEnd

        $a = 5;
    }

}
