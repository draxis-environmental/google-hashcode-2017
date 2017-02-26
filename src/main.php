<?php
/**
 * Created by PhpStorm.
 * User: lephleg
 * Date: 26/02/2017
 * Time: 00:35
 */

include_once 'FileReader.php';
include_once 'FileWriter.php';

class HashCode
{

    protected $input;

    protected $videos = array();

    protected $servers = array();

    protected $endpoints = array();


    public function __construct($filename)
    {
        $this->input = $filename;
    }


    public function run()
    {
        $reader = new FileReader($this->input);
        $this->populate($reader->getData());
        $this->calculate();
        $writer = new FileWriter('../output/kittens.out', $this->servers);
        $writer->write();
    }


    protected function calculate()
    {
        foreach ($this->videos as $v => $video) {
            foreach ($this->endpoints as $i => $endpoint) {
                $sum[$i] = 0;
                foreach ($endpoint->servers as $j => $server) {
                    $requests = $video->getEndpointRequests($endpoint);
                    $latency = $server->getEndpointLatency($endpoint);
                    $cost = $requests*$latency;
                    $sum[$i] = $sum[$i] + $cost;
                }
            }
        }

    }

    protected function populate($data) {

        $this->videos = $data['videos'];
        $this->servers = $data['servers'];
        $this->endpoints = $data['endpoints'];

    }

}

$hash = new HashCode('../input/kittens.in');
$hash->run();
