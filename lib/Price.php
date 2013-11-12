<?php

/*
    Helper class to help handle json data from EMDR/Redis. 
    I got tired of handling arrays and junk, so this helps 
    and makes code a little cleaner.
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