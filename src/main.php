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
        $writer->write();
    }


    protected function populate($data)
    {

        $this->videos    = $data['videos'];
        $this->servers   = $data['servers'];
        $this->endpoints = $data['endpoints'];

    }


    protected function calculate()
    {
        usort($this->videos, 'sort_videos_by_requests');

        foreach ($this->videos as $v => $video) {

            foreach($video->getRequests() as $request)
            {
                $id = $request->endpoint->getClosestFreeServer($video);
                if($id >= 0) {
                    $closestServer = $this->servers[$id];

                    if(is_object($closestServer))
                    {
                        if($closestServer->hasSpace($video)) {
                            $closestServer->addVideo($video);
                        }
                    }

                }
            }

        }

        foreach($this->servers as $s)
        {
            print_r($s->getId() . ' ');
            foreach ($s->getVideos() as $video) {
                print_r($video->getId(). ' ');
            }
            print_r("\n");
        }

    }

}

ini_set ('max_execution_time', 1600 );
ini_set('memory_limit','912M');

$hash = new HashCode("../input/$argv[1].in");
$hash->run();
