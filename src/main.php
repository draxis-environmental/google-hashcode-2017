<?php

include_once 'FileReader.php';
include_once 'FileWriter.php';

class HashCode
{

    protected $input;

    protected $videos = [];

    protected $servers = [];

    protected $endpoints = [];


    public function __construct($filename)
    {
        $this->input = $filename;
    }


    public function run()
    {
        global $argv;
        $reader = new FileReader($this->input);
        $this->populate($reader->getData());
        //$this->calculate();
        $writer = new FileWriter("../output/$argv[1].out", $this->servers);
        $writer->printServersWithEndpoints();
    }


    protected function populate($data)
    {

        $this->videos    = $data['videos'];
        $this->servers   = $data['servers'];
        $this->endpoints = $data['endpoints'];

    }


    protected function calculate()
    {
        foreach ($this->videos as $v => $video) {
            foreach ($this->servers as $i => $server) {
                $sum[$i] = 0;
                foreach ($server->getAllEndpoints() as $endpointId) {
                    $requests = $video->getEndpointRequests($endpointId);
                    $latency  = $server->getEndpointLatency($endpointId);
                    $cost     = $requests * $latency;
                    $sum[$i]  = $sum[$i] + $cost;
                }
            }
        }

    }

}

$hash = new HashCode("../input/$argv[1].in");
$hash->run();
