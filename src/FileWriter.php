<?php

class FileWriter
{

    protected $filename;

    protected $videosOnServers;


    public function __construct($filename, $videosOnServers)
    {
        $this->filename = $filename;
        $this->videosOnServers  = $videosOnServers;
    }


    public function write()
    {

        $totalServers = count($this->videosOnServers);

        $myfile = fopen($this->filename, "w") or die("Unable to open file!");
        $txt = $this::print_line($totalServers);
        fwrite($myfile, $txt);

        foreach ($this->videosOnServers as $sid => $server) {
            fwrite($myfile, $sid);
            fwrite($myfile, ' ');
            foreach ($this->videosOnServers[$sid] as $vid => $video) {
                fwrite($myfile, $video);
                fwrite($myfile, ' ');
            }
            $txt = $this::print_line();
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }


    protected static function print_line($line = '')
    {
        return $line . "\n";
    }

}