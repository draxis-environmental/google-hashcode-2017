<?php

include_once 'Server.php';

class Endpoint
{

    protected $id;

    protected $servers = [];

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


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

}