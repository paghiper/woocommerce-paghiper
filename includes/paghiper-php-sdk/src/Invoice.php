<?php 

namespace PagHiper;

class Invoice extends PagHiper {

    /**
     * @var \WebMaster\PagHiper\PagHiper;
     */
    protected $paghiper;

    public function __construct(PagHiper $paghiper) {
        $this->paghiper = $paghiper;
    }
	
}