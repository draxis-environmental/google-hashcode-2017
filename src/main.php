<?php

include_once 'FileReader.php';
include_once 'FileWriter.php';
include_once 'Helpers.php';

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
        $this->calculate();
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

            foreach($video->getRequests() as $request)
            {
                $closestServer = $this->servers[$request->endpoint->getClosestFreeServer($video)];

                if($closestServer->hasSpace($video)) {
                    $closestServer->addVideo($video);
                }

            }

            /*foreach ($this->servers as $i => $server) {
                $sum[$i] = 0;
                foreach ($server->getAllEndpoints() as $endpointId) {
                    $requests = $video->getEndpointRequests($endpointId);
                    $latency  = $server->getEndpointLatency($endpointId);
                    $cost     = $requests * $latency;
                    $sum[$i]  = $sum[$i] + $cost;
                }
            }*/
        }

        foreach($this->servers as $s)
        {
            echo $s->getId().' =>';
            print_r($s->video_ids);
        }

    }

}

ini_set ('max_execution_time', 1600 );
ini_set('memory_limit','912M');

$hash = new HashCode("../input/$argv[1].in");
$hash->run();
