<?php

include_once 'Endpoint.php';
include_once 'Video.php';
include_once 'Server.php';
include_once 'Request.php';

class FileReader
{

    protected $file;

    protected $totalVideos;

    protected $videos = [];

    protected $totalEndpoints;

    protected $endpoints = [];

    protected $totalServers;

    protected $servers = [];

    protected $capacity;

    protected $totalRequests = [];


    public function __construct($input)
    {
        $this->file = new SplFileObject($input);
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
        $videos = $this->nextLine();
        foreach ($videos as $index => $video) {
            $this->videos[$index] = new Video($index, $video);
        }

        //parse endpoints
        for ($i = 0; $i < $this->totalEndpoints; $i++) {

            $endpointData        = $this->nextLine();
            $this->endpoints[$i] = new Endpoint($i, $endpointData[0]);
            $endpointServers     = $endpointData[1];

            //parse endpoint's servers
            for ($j = 0; $j < $endpointServers; $j++) {

                $serverData = $this->nextLine();

                //is this a first-appearing server?
                if (array_key_exists($serverData[0], $this->servers)) {
                    $server = $this->servers[$serverData[0]];
                    $server->addEndpointLatency($i, $serverData[1]);
                } else {
                    $server                        = new Server($serverData[0], $this->capacity, $i, $serverData[1]);
                    $this->servers[$serverData[0]] = $server;
                }

                $this->endpoints[$i]->addServer($server);
            }

        }

        //parse requests
        for ($i = 0; $i < $this->totalRequests; $i++) {
            $requestData = $this->nextLine();
            $req         = new Request($requestData[1], $requestData[2]);
            $this->videos[$requestData[0]]->addRequest($req);
        }

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