<?php

require_once 'Request.php';

class Video
{

    public $id;

    public $size;

    public $requests = [];


    public function __construct($id, $size)
    {
        $this->id       = $id;
        $this->size     = $size;
        $this->requests = [];
    }


    public function addRequest(Request $request)
    {
        if ( ! in_array($request, $this->requests, true)) {
            array_push($this->requests, $request);
        }
    }

}