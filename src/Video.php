<?php

require_once 'Request.php';


class Video
{

    protected $id;

    protected $size;

    protected $requests = [];


    public function __construct($id, $size)
    {
        $this->id   = $id;
        $this->size = $size;
    }

    public function getId()
    {
        return $this->id;
    }


    public function addRequest(Request $request)
    {
        array_push($this->requests, $request);
    }


    public function getEndpointRequests(Endpoint $endpoint)
    {
        $id = $endpoint->getId();

        $requests = array_filter($this->requests, function ($e) use (&$id) {
            return $e->getId() == $id;
        });

        return ($requests) ? $requests[0]->getTotal() : null;

    }

    public function getRequests()
    {
        usort($this->requests, 'sort_requests_by_total');
        return $this->requests;
    }


    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

}