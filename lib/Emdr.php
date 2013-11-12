<?php

/*
    Simple helper class that extends Redis
    Only does a connection to the Redis server and formats a key
    
    Added caching as well. Although accessing Redis values is very fast, doing 
    them hundreds or times adds up. The class implements a simple caching in the 
    form of 
        
        typeID => Redis string
    
    Whenever we request info with EMDR::get(), we first check to see if the 
    price has already been fetched from the cache array and return it if true.
    
    This helps very large LP stores, as many of them have offers that use the 
    same required items. This especially helps with blueprint calculations, as 
    blueprints always use a basic mineral, and it's silly to call that same 
    price data for the same mineral 18 different times for 18 blueprints. 
    
    Calculations for the larget LP Store at this time (1000180, 368 offers with 
    18 BPCs, 752 calls to EMDR::get()) dropped by 0.3 seconds on average.
*/

class Emdr extends Redis {

    public  $regionID;
    private $version;
    private $cache;

    public function __construct($regionID, $emdrVersion = 1)
    {
        parent::__construct();
        
        $this->regionID = $regionID;
        $this->version  = $emdrVersion;

        parent::connect('localhost', 6379) or die ("Could not connect to Redis server");
        parent::select(Config::emdrRedis);
    }
    
    public function get($typeID) {
        if (isset($this->cache[$typeID])) {
            return $this->cache[$typeID]; }
        else {
            $string = 'emdr-'.$this->version.'-'.$this->regionID.'-'.$typeID;
            return $this->cache[$typeID] = parent::get($string);
        }
    }
}

?>