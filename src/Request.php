<?php

class Request
{

    public $id;

    public $vid;

    public $eid;

    public $total;


    public function __construct($id, $vid, $eid, $total)
    {
        $this->id    = $id;
        $this->vid   = $vid;
        $this->eid   = $eid;
        $this->total = $total;
    }

}