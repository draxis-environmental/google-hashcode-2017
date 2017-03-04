<?php

include_once 'FileReader.php';
include_once 'FileWriter.php';
include_once 'Helpers.php';

class HashCode
{

    protected $input;

    protected $debug;

    protected $progress;

    protected $videos = [];

    protected $totalVideos;

    protected $servers = [];

    protected $totalServers;

    protected $endpoints = [];

    protected $totalEndpoints;

    protected $videosOnServers = [];

    protected $serverMap;
    protected $hasClusters;


    public function __construct($filename, $flag = 0)
    {
        $this->input = $filename;
        if ($flag == 'full') {
            $this->debug = $this->progress = 1;
        } elseif ($flag == 'debug') {
            $this->debug = 1;
        } elseif ($flag == 'progress') {
            $this->progress = 1;
        } else {
            $this->debug = $this->progress = 0;
        }
    }


    public function run()
    {
        $this->debug and $time_pre = microtime(true);

        global $argv;
        $reader = new FileReader($this->input, $this->debug, $this->progress);
        $this->populate($reader->getData());

        if ($this->debug) {
            print_r('Servers count: ' . count($this->servers) . "\n");
            print_r('Videos count: ' . count($this->videos) . "\n");
            print_r('Endpoints count: ' . count($this->endpoints) . "\n");
            print_r("\n");
        }

        $this->preprocess();
        //$this->process();
        //$this->hasClusters and $this->postprocessing();

        $writer = new FileWriter("../output/$argv[1].out", $this->videosOnServers);
        $writer->write();

        if ($this->debug) {
            $time_post = microtime(true);
            $exec_time = $time_post - $time_pre;
            print_r("\n");
            print_r('Execution time: ' . $exec_time . "sec \n");
        }
    }


    protected function populate($data)
    {
        $this->videos         = $data['videos'];
        $this->totalVideos    = $data['totalVideos'];
        $this->servers        = $data['servers'];
        $this->totalServers   = $data['totalServers'];
        $this->endpoints      = $data['endpoints'];
        $this->totalEndpoints = $data['totalEndpoints'];
    }


    protected function process()
    {
        $servers = $this->servers;

        $scores = [];
        foreach ($servers as $sid => $server) {
            if($this->debug) {
                print_r("===============\n");
                print_r("SERVER-id: $sid\n");
                print_r("===============\n");
            }
            foreach ($server->endpoints as $eid => $endpoint) {
                $latencyDiff = $endpoint->latency;
                if($this->debug) {
                    print_r("------------------\n");
                    print_r("ENDPOINT-id: $eid\n");
                    print_r("ENDPOINT-lat: $latencyDiff\n");
                    print_r("------------------\n");
                }
                //find the endpoint connections linked to this server
                foreach ($endpoint->connections as $con => $connection) {
                    if (isset($servers[$connection->sid])) {
                        if ($servers[$connection->sid]->id == $sid) {
                            $latencyDiff -= $connection->latency;
                            if($this->debug) {
                                print_r('# Server id matched for connection id: ' . $con . "\n");
                                print_r('# Latency difference: ' . $latencyDiff . "\n");
                            }
                            break;
                        }
                    }
                }

                //if there are requests, compute score
                foreach ($endpoint->requests as $rid => $request) {
                    $score = 0;

                    if (isset($scores[$request->vid][$sid])) {
                        $score = $scores[$request->vid][$sid];
                    }

                    $score                       -= $request->total * $latencyDiff / $this->videos[$request->vid]->size;
                    $scores[$request->vid][$sid] = $score;

                    $this->debug and print_r('# Score key: [vid: ' . $request->vid . '][sid: ' . $sid . "] ---- Score: " . $score . "\n");

                    unset($endpoint->requests[$rid]);
                    unset($score);

                }
                unset($server->endpoints[$eid]);
            }
            unset($servers[$sid]);
        }

        $pq = new SplMinHeap();
        foreach ($scores as $vid => $vid_array) {
            foreach ($vid_array as $sid => $item) {
                $pq->insert([$scores[$vid][$sid], $vid, $sid]);
                if($this->debug) {
                    print_r('VID: ' . $vid . ' - SID: ' . $sid . "\n");
                    print_r('SCORE: ' . $scores[$vid][$sid] . "\n");
                }
            }
        }

        //now we have the heap with the scores
        $this->debug and print_r("Size of the heap: " . $pq->count() . " / " . $this->totalVideos . " * " . $this->totalServers . "\n");

        //$index = 0;
        while ( ! $pq->isEmpty()) {
            //print_r($index . "\n");
            $scoreItem = $pq->extract();
            $score     = $scoreItem[0];
            $vid       = $scoreItem[1];
            $sid       = $scoreItem[2];

            if($this->debug) {
                print_r("\n");
                print_r("Score: $score \n");
                print_r("Video: $vid \n");
                print_r("Server: $sid \n");
                //print_r("New Score: $scores[$vid][$sid] \n");
            }

            // check if score has changed
            // insert to heap if score has changed
            if (isset($scores[$vid][$sid]) && ($scores[$vid][$sid] > $score)) {
                $pq->insert([$scores[$vid][$sid], $vid, $sid]);
                $this->debug and print_r('yo' . "\n");
                continue;
            }

            // instantiate the array on first iteration to avoid php warnings
            if ( ! isset($this->videosOnServers[$sid])) {
                $this->videosOnServers[$sid] = [];
            }

            $serverSize = 0;
            foreach ($this->videosOnServers[$sid] as $id => $item) {
                $serverSize += $this->videos[$id]->size;
            }

            if ($serverSize + $this->videos[$vid]->size > $this->servers[$sid]->size) {
                $this->debug and print_r("server (#$sid) is full, continue\n");
                //$index++;
                continue;
            }

            if (array_search($vid, $this->videosOnServers[$sid])) {
                $this->debug and print_r("duplicate video (#$vid), break\n");
                break;
            }

            array_push($this->videosOnServers[$sid], $vid);

            // now update neighboring server which need to hold the same video
            $server = $this->servers[$sid];
            foreach ($server->endpoints as $eid => $endpoint) {
                foreach ($endpoint->connections as $ncon => $nconnection) {
                    if ($nconnection->sid == $sid) {
                        continue;
                    }
                    if (isset($scores[$vid][$nconnection->sid])) {
                        $latencyDiff = $endpoint->latency;
                        foreach ($endpoint->connections as $con => $connection) {
                            if (isset($servers[$connection->sid])) {
                                if ($servers[$connection->sid]->id == $nconnection->sid) {
                                    $latencyDiff -= $connection->latency;
                                    break;
                                }
                            }
                        }
                        $numberOfRequests = 0;
                        foreach ($this->videos[$vid]->requests as $rid => $request) {
                            if ($request->eid == $eid) {
                                $numberOfRequests = $request->total;
                                break;
                            }
                        }
                        $score = $scores[$vid][$sid];
                        $score += $numberOfRequests * $latencyDiff / $this->videos[$vid]->size;
                        $scores[$vid][$sid] = $score;

                    }
                }
            }
            //$index++;
        }

    }

    protected function preprocess()
    {

        $serverPoints = [];

        print_r("Generating server-endpoint mapping matrix...\n");
        for ($i = 0; $i < count($this->servers); $i++) {
            for ($j = 0; $j < count($this->endpoints); $j++) {
                foreach ($this->servers[$i]->endpoints as $eid => $endpoint) {
                    if ($endpoint->id == $j) {
                        $serverPoints[$i][$j] = 1;
                        break;
                    } else {
                        $serverPoints[$i][$j] = 0;
                    }
                }
            }
            $this->progress and show_progress($i, count($this->servers), 'servers preprocessed');
        }

        if ($this->debug) {
            print_r("\n");
            for ($i = 0; $i < count($this->servers); $i++) {
                if ($i < 10) {
                    print_r(" " . $i . " | ");
                } else {
                    print_r($i . " | ");
                }
                for ($j = 0; $j < count($this->endpoints); $j++) {
                    print_r($serverPoints[$i][$j] . " ");
                }
                print_r("\n");
            }
            print_r("\n");
        }

        //scan for unique rows
        $unique = array_unique($serverPoints,SORT_REGULAR);
        $this->debug and print_r('Unique rows: ' . count($unique) . "\n");

        $serverMap = [];
        foreach ($this->servers as $sid => $server) {
            $serverMap[$sid] = $sid;
        }
        $this->debug and print_r("ServerMap: \n");
        $this->debug and print_r($serverMap);

        if (count($unique) < count($this->servers)) {
            $this->debug and print_r("Requires clustering.\n");

            $serversOld = $this->servers;
            $servers = [];

            // now join clusters
            for ($i = 0; $i < count($this->servers); $i++) {
                $index = key_exists($i,$unique);
                if ($index) {
                    $this->debug and print_r("Index found: $i\n");
                    $serverMap[$i] = $i;
                    $servers[$i] = new Server($i, $servers[$i]->size += $serversOld[$i]->size);
                    $servers[$i]->endpoints = $serversOld[$i]->endpoints;
                }
            }
            $this->servers = $servers;
            $this->debug and print_r("Clusters:\n");
            $this->debug and print_r($serverMap);
            $this->debug and print_r($servers);
        }

        $this->serverMap = $serverMap;
        $this->hasClusters = count($unique) < count($this->servers);

    }

    protected function postprocessing()
    {


    }
}

ini_set('max_execution_time', 1600);
ini_set('memory_limit', '912M');

$hash = new HashCode("../input/$argv[1].in", $argv[2]);
$hash->run();