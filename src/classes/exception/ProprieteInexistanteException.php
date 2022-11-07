<?php

namespace iutnc\netVOD\exception;

class ProprieteInexistanteException extends \Exception
{

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {
        parent::__construct($string);
    }
}