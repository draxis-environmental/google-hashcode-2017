<?php

include_once 'FileReader.php';
include_once 'FileWriter.php';
include_once 'Helpers.php';

class HashCode
{

    protected $input;

    protected $videos = [];
    protected $totalVideos;

    protected $servers = [];
    protected $totalServers;

    protected $endpoints = [];
    protected $totalEndpoints;

    public function __construct($filename)
    {
        $this->input = $filename;
    }


    public function run()
    {

        $time_pre = microtime(true);

        global $argv;
        $reader = new FileReader($this->input);
        $this->populate($reader->getData());
        $this->process();

        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        print_r('Execution time: ' . $exec_time . "sec \n");
        //$writer = new FileWriter("../output/$argv[1].out", $this->servers);
        //$writer->write();
    }


    protected function populate($data)
    {

        $this->videos           = $data['videos'];
        $this->totalVideos      = $data['totalVideos'];
        $this->servers          = $data['servers'];
        $this->totalServers     = $data['totalServers'];
        $this->endpoints        = $data['endpoints'];
        $this->totalEndpoints   = $data['totalEndpoints'];

    }


    protected function process()
    {
        print_r('Servers count: ' . count($this->servers) . "\n");
        print_r('Videos count: ' . count($this->videos). "\n");
        print_r('Endpoints count: ' . count($this->endpoints). "\n");


        $videosOnServers = [];
        $scores = [];
        $scoreItem = [];

        foreach ($this->servers as $sid => $server) {

            $scoresForServer = new SplObjectStorage();
            //if there are requests, compute score
            foreach ($server->endpoints as $eid => $endpoint) {
                $latencyDiff = $endpoint->latency;
                foreach ($endpoint->connections as $con => $connection) {
                    if (isset($this->servers[$connection->sid])) {
                        if ($this->servers[$connection->sid]->id == $sid) {
                            $latencyDiff -= $connection->latency;
                            break;
                        }
                    }
                }
                foreach ($endpoint->requests as $rid => $request) {
                    $key = new ArrayObject(['vid' => $request->vid, 'sid' => $sid]);
                    $score = 0;

                    if ($scoresForServer->contains($key))
                        $score = $scoresForServer[$key];

                    $score = $request->total * $latencyDiff / $this->videos[$request->vid]->size;
                    $scoresForServer[$key] = $score;

                    unset($endpoint->requests[$rid]);
                    unset($key);
                    unset($score);
                }
                unset($server->endpoints[$eid]);
            }
            array_push($scores,$scoresForServer);
            unset($scoresForServer);
            unset($this->servers[$sid]);
        }

        $pq = new SplMinHeap();
        foreach ($scores as $scid => $score) {
            foreach ($score as $id => $obj) {
                $pq->insert([$score, $score->current()["vid"], $score->current()["sid"]]);
            }
        }

        //now we have the heap with the scores
        print_r("Size of the heap: " . $pq->count() . " / " . $this->totalVideos . " * " . $this->totalServers . "\n");

    }

    protected function preprocess() {

        $serverPoints = [];
        foreach ($this->servers as $sid => $server) {
            foreach ($server->endpoints as $eid => $endpoint) {
                $serverPoints[$sid][$eid] = 1;
            }
        }

    }

}

ini_set('max_execution_time', 1600);
ini_set('memory_limit', '912M');

$hash = new HashCode("../input/$argv[1].in");
$hash->run();
