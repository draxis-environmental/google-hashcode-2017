<?php

include_once 'Endpoint.php';
include_once 'Video.php';
include_once 'Server.php';
include_once 'Request.php';
include_once 'Connection.php';

class FileReader
{

    protected $file;

    protected $debug;

    protected $progress;

    protected $totalVideos;

    protected $videos = [];

    protected $totalEndpoints;

    protected $endpoints = [];

    protected $totalServers;

    protected $servers = [];

    protected $capacity;

    protected $totalRequests = [];


    public function __construct($input, $debug = 0, $progress = 0)
    {
        $this->file = new SplFileObject($input);
        $this->debug = $debug;
        $this->progress = $progress;
    }


    public function getData()
    {

        $this->parse();

        return [
            'totalVideos'    => $this->totalVideos,
            'videos'         => $this->videos,
            'totalEndpoints' => $this->totalEndpoints,
            'endpoints'      => $this->endpoints,
            'totalServers'   => $this->totalServers,
            'servers'        => $this->servers,
            'capacity'       => $this->capacity
        ];
    }


    protected function parse()
    {
        //parse header
        $header               = $this->nextLine();
        $this->totalVideos    = $header[0];
        $this->totalEndpoints = $header[1];
        $this->totalRequests  = $header[2];
        $this->totalServers   = $header[3];
        $this->capacity       = $header[4];

        //parse videos
        print_r("Parsing videos...\n");
        $videos = $this->nextLine();
        foreach ($videos as $index => $video) {
            $this->videos[$index] = new Video($index, $video);
        }

        //parse endpoints
        print_r("Parsing endpoints...\n");
        for ($i = 0; $i < $this->totalEndpoints; $i++) {
            $endpointData = $this->nextLine();
            $this->endpoints[$i] = new Endpoint($i, $endpointData[0]);
            $endpointServers = $endpointData[1];

            //parse endpoint's servers
            for ($j = 0; $j < $endpointServers; $j++) {

                $serverData = $this->nextLine();

                //is this a first-appearing server?
                if (array_key_exists($serverData[0], $this->servers)) {
                    $server = $this->servers[$serverData[0]];
                    $server->addEndpoint($this->endpoints[$i]);
                } else {
                    $server = new Server($serverData[0], $this->capacity);
                    $this->servers[$serverData[0]] = $server;
                    $server->addEndpoint($this->endpoints[$i]);
                }

                $this->endpoints[$i]->addConnection(new Connection($j, $server->id, $serverData[1]));
            }

            $this->progress and show_progress($i,$this->totalEndpoints, 'endpoints');
        }

        //parse requests
        print_r("Parsing requests...\n");
        for ($i = 0; $i < $this->totalRequests; $i++) {
            $requestData = $this->nextLine();
            $req = new Request($i, $requestData[0], $requestData[1], $requestData[2]);

            $this->videos[$req->vid]->addRequest($req);
            $this->endpoints[$req->eid]->addRequest($req);

            $this->progress and show_progress($i,$this->totalRequests, 'requests');
        }

        print_r("\n");
    }


    protected function nextLine()
    {
        $line = $this::stripEndLine($this->file->fgets());
        return explode(' ', $line);
    }

    protected static function stripEndLine($string) {
        return $string = trim(preg_replace('/\s\s+/', ' ', $string));
    }

}