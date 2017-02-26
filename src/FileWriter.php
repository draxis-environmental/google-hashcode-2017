<?php

class FileWriter
{
    protected $filename;
    protected $servers;

    public function __construct($filename, $servers)
    {
        $this->filename = $filename;
        $this->servers = $servers;
    }

    public function write() {

        $totalServers = count($this->servers);

        $myfile = fopen($this->filename, "w") or die("Unable to open file!");
        $txt = $this::print_line($totalServers);
        fwrite($myfile, $txt);

        foreach ($this->servers as $server) {
            fwrite($myfile, $server->getId());
            fwrite($myfile, ' ');
            foreach ($server->getVideos() as $video) {
                fwrite($myfile, $video->id);
                fwrite($myfile, ' ');
            }
            $txt = $this::print_line();
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }

    protected static function print_line($line = '') {
        return $line . "\n";
    }


}