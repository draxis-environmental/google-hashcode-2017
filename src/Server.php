<?php

class Server
{

    protected $id;

    protected $latencies = array();

    protected $videos = array();


    public function __construct($id, $endpointId = null, $latency = null)
    {
        $this->id = $id;
        if ( ! is_null($endpointId) && ! is_null($latency)) {
            $this->latencies[$endpointId] = $latency;
        }
    }


    public function addEndpointLatency($endpointId, $latency)
    {
        $this->latencies[$endpointId] = $latency;
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
    public function getVideos()
    {
        return $this->videos;
    }


    /**
     * @return array
     */
    public function getEndpointLatency(Endpoint $endpoint)
    {
        $id = $endpoint->getId();
        if (array_key_exists($id,$this->latencies)) {
            return $this->latencies[$id];
        } else {
            return null;
        }
    }
}