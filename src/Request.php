<?php

class Request
{

    protected $endpointId;

    protected $total;


    public function __construct($endpointId, $total)
    {
        $this->endpointId = $endpointId;
        $this->total      = $total;
    }

}