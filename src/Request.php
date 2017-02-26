<?php

class Request
{

    protected $id;

    protected $endpointId;

    protected $total;


    public function __construct($endpointId, $total)
    {
        $this->endpointId = $endpointId;
        $this->total      = $total;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

}