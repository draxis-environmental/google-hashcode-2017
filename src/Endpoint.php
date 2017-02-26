<?php

include_once 'Server.php';

class Endpoint
{

    protected $id;

    protected $servers = array();

    protected $dataCenterLatency;


    public function __construct($id, $latency)
    {
        $this->id                = $id;
        $this->dataCenterLatency = $latency;
    }


    /**
     * @param Server $server
     */
    public function addServer(Server $server)
    {
        array_push($this->servers, $server);
    }

}