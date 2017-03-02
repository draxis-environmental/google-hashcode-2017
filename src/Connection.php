<?php

class Connection
{
    public $id;

    public $sid;

    public $latency;

    public function __construct($id, $sid, $latency)
    {
        $this->id    = $id;
        $this->sid   = $sid;
        $this->latency = $latency;
    }


}