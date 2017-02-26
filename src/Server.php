<?php

class Server
{

    protected $id;

    protected $latencies = [];

    protected $videos = [];

    protected $free;


    public function __construct($id, $free, $endpointId = null, $latency = null)
    {
        $this->id   = $id;
        $this->free = $free;
        if ( ! is_null($endpointId) && ! is_null($latency)) {
            $this->latencies[$endpointId] = $latency;
        }
    }

    public function addEndpointLatency($endpointId, $latency)
    {
        $this->latencies[$endpointId] = $latency;
    }


    public function addVideo(Video $video)
    {
        array_push($this->videos, $video);
        $this->free = $this->free - $video->getSize();
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
    public function getFree()
    {
        return $this->free;
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
        if (array_key_exists($id, $this->latencies)) {
            return $this->latencies[$id];
        } else {
            return null;
        }
    }


    /**
     * @return array
     */
    public function getAllEndpoints()
    {
        return array_keys($this->latencies);
    }
}