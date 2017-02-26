<?php

class Request
{

    protected $id;

    public $endpoint;

    public $total;


    public function __construct(Endpoint $endpoint, $total)
    {
        $this->endpoint = $endpoint;
        $this->total    = $total;
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