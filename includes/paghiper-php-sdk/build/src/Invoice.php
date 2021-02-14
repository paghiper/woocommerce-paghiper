<?php

namespace PagHiper;

class Invoice extends \PagHiper\PagHiper
{
    /**
     * @var \WebMaster\PagHiper\PagHiper;
     */
    protected $paghiper;
    public function __construct(\PagHiper\PagHiper $paghiper)
    {
        $this->paghiper = $paghiper;
    }
}
