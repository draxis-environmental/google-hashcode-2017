<?php

class Endpoint
{

    public $id;

    public $latency;

    public $connections = [];

    public $requests = [];


    public function __construct($id, $latency)
    {
        $this->id          = $id;
        $this->latency     = $latency;
        $this->connections = [];
        $this->requests    = [];
    }


    public function addConnection(Connection $con)
    {
      $this->connections[$con->id] = $con;
    }

    public function addRequest(Request $req)
    {
        $this->requests[$req->id] = $req;
    }

}