<?php

include_once 'Server.php';

class Endpoint
{

    protected $id;

    protected $servers = [];

    protected $dataCenterLatency;

    protected $serverLatency;


    public function __construct($id, $latency)
    {
        $this->id                = $id;
        $this->dataCenterLatency = $latency;
        $this->serverLatency     = array();
    }


    /**
     * @param Server $server
     */
    public function addServer(Server $server)
    {
      $this->servers[$server->getId()] = $server;
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


    public function addServerLatency($serverId,$latency)
    {
        $this->serverLatency[$serverId] = $latency;
    }

    public function getServersLatency()
    {
        return $this->serverLatency;
    }

    public function getClosestFreeServer(Video $video, $excludeFullServers = array())
    {
        if(sizeof($excludeFullServers) >= sizeof($this->serverLatency) )
            return -1; // All cache servers are full , keep it in datacenter

        $tmpServersLatency = $this->serverLatency;

        foreach($excludeFullServers as $id => $exServerId) {
            unset($tmpServersLatency[$exServerId]);
        }

        if (empty($tmpServersLatency)) {
            return -1; // No cache servers linked with this endpoint
        }

        $serverId = array_keys($tmpServersLatency, min($tmpServersLatency));

        if($tmpServersLatency[$serverId[0]] >= $this->dataCenterLatency )
            return -1; // Datacenter is the closest, keep it in datacenter

        if($this->servers[$serverId[0]]->hasSpace($video)) 
            return $serverId[0];
        else
        {
            array_push($excludeFullServers,$serverId[0]);
            $this->getClosestFreeServer($video,$excludeFullServers);
        }

    }

}