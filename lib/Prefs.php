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

    const __default = self::sell;
    
    const sell = 1;
    const buy  = 2;
    
    private static $instance = null;
    private static $prefs = array();
    
    function __construct() { }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Prefs();
        }
        return self::$instance;
    }
    
    public static function setMarketMode($order, $req, $mat){
        /* set prefs here */
    }
    
    public function __get($pref) {
            
    }
    
    private static function save() {
        /* save cookie */
    }
    
    protected function __clone() { }
}

?>