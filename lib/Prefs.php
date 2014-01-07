<?php

/*
    Preferences singleton - WIP
    
    Basic structure:
    
    When initialized, get cookie and load into array. If no cookie, use default 
    settings, but don't save cookie until after user chooses 'save prefs'. When 
    setting new prefs, set array first, then call save() method to save/overwrite 
    cookie.
    
    __get() should get specific preference in pref array
*/

class Prefs {

    private static $instance = null;
    public  static $prefs    = array();
    private static $default  = array(
                    'marketOffer' => 'sell',
                    'marketReq'   => 'sell',
                    'marketMat'   => 'sell',
                    'region'      => 10000002);
    
    function __construct() { 
        if (isset($_COOKIE['prefs'])){
            self::$prefs = array_merge(self::$default, unserialize($_COOKIE['prefs']))    ;
        }
        else {
            self::$prefs = self::$default; }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Prefs();
        }
        return self::$instance;
    }
    
    public static function setMarketMode($offer, $req, $mat){
        self::$prefs['marketOffer'] = $offer;
        self::$prefs['marketReq']   = $req;
        self::$prefs['marketMat']   = $mat;
    }
    
    public static function setRegion($regionID){
        self::$prefs['region'] = $regionID;
    }
    
    public static function get($pref) {
        return self::$prefs[$pref];
    }
    
    public static function save() {
        if (setcookie('prefs', serialize(self::$prefs), time()+60*60*24*30*365*5, BASE_PATH)) {
            return true; }
        return false;
    }
    
    protected function __clone() { }
}

?>