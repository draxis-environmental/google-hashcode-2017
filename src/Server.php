<?php

class Server
{

    public $id;

    public $size;

    public $endpoints = [];

    public $videos = [];


    public function __construct($id, $size)
    {
        $this->id        = $id;
        $this->size      = $size;
        $this->endpoints = [];
    }


    public function addEndpoint(Endpoint $endpoint)
    {
        if ( ! in_array($endpoint, $this->endpoints, true)) {
            array_push($this->endpoints, $endpoint);
        }
    }


    public function addVideo(Video $video)
    {
        if ( ! in_array($video, $this->videos, true)) {
            array_push($this->videos, $video);
        }
    }

}