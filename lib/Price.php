<?php

/*
    Helper class tp help handle jsone data from EMDR/Redis. 
    I wonder if this will go places eventually...
    
    
    

    Probably not.
*/

class Price {
    public  $data;

    function __construct($rawString) {
        $this->data = json_decode($rawString, true);
        
        if (empty($this->data)){
           throw new Exception('No pricing data.'); }
    }
    
    public function __get($name) {
        return $this->data['orders'][$name]; }
}