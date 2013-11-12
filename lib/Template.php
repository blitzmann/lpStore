<?php

/*
    Helper class for TinyButStrong
    
    Allows you to specify template name and directories, so that template 
    switching may be used if desired simply by changing Template::name
*/

class Template extends TinyButStrong {
    
    public $dir;
    public $name;

    public function __construct($tplDir, $tplName) {
        parent::__construct();
        
        # Sets base template directory, and then template name
        # @todo: check if path exists
        $this->dir  = $tplDir;
        $this->name = $tplName;
    }
    
    public function LoadTemplate($file, $charset = '') {
        parent::LoadTemplate($this->dir.'/'.$this->name.'/'.$file, $charset); }
}