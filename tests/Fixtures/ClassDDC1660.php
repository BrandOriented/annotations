<?php

namespace Doctrine\AnnotationsTests\Fixtures;

class ClassDDC1660
{
    /**
     * @var     string
     * @since   2.0
     * @version 1
     */
    public $foo;

    /**
     * @param   string
     * @return  string
     * @since   2.0
     * @version 1
     */
    public function bar($param)
    {
        return null;
    }

}