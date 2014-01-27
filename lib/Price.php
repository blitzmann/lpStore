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
        
        try {
        if (empty($this->data)){
           throw new Exception('No pricing data.'); }
        } catch (Exception $e) {
            echo 'Opps... Something happened. Please try refreshing page.<br /><br /> Error message: ',  $e->getMessage();
            die(var_dump($e));
        }
    }

    public function __get($name) {
        return $this->data['orders'][$name]; }
}