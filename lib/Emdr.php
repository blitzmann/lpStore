<?php

/*
    EMDR singleton class for getting prices from a Redis database.
    
    Added in-class caching as well. Although accessing Redis values is very fast,
    doing them hundreds of times adds up. The class implements a simple caching 
    in the form of 
        
        regionID => typeID => Redis string
    
    Whenever we request info with EMDR::get(), we first check to see if the 
    price has already been fetched from the cache array and return it if true.
    
    This helps very large LP stores, as many of them have offers that use the 
    same required items. This especially helps with blueprint calculations, as 
    blueprints always use a basic mineral, and it's silly to call that same 
    price data for the same mineral 18 different times for 18 blueprints. 
    
    Calculations for the largest LP Store at this time (1000180, 368 offers with 
    18 BPCs, 752 calls to EMDR::get()) dropped by 0.3 seconds on average.
*/

class Emdr {

    protected static $regionID;
    private   static $cache = array();
    private   static $instance = null;
    private   $_redis;

    public static $regions = array(); // available regions
    
    function __construct() { }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new RedisCache();
        }
        return self::$instance;
    }
    
    public static function setRegion($regionID){
        self::$regionID = $regionID;
    }
    
    public static function get($typeID) {
        # Check in cache
        if (isset(self::$cache[self::$regionID][$typeID])) {
            return self::$cache[self::$regionID][$typeID]; }

        # not in cache
        $emdr = Emdr::getInstance();
        $string = 'emdr-'.Config::emdrVersion.'-'.self::$regionID.'-'.$typeID;
        
        $data = '{"orders": {"generatedAt": 1, "sell":[50000,13], "buy": [30000, 43]}, "history": []}'; //$emdr->get($string);
        self::$cache[self::$regionID][$typeID] = $data;
        return $data;
    }

    protected function __clone() { }
}

?>