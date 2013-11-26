<?php

/*
    Getting icons for a large store takes half a minute. Fuck me.
    
    Rethink this. Either pre-cache icons on a regular basis (with expansions/updates)
    Or go back to the failed attempt of assembling the icons locally with the 
    base icon and meta tag info.
    
    This class is a mess anyway with half-baked ideas.
    
    I doubt its worth it, but try to serve up EVE Image Server if no cache, and 
    run a background process to cache ones that were'nt cached before.
*/

class EveIcon {

    private static $instance = null;
    private static $container = array();

    const TYPE2ICON_FILE = 'static/type2icon.json';
    const ICONS_DIR      = 'icons/';
    
    protected function __construct() {
        self::$container = json_decode(file_get_contents('static/type2icon.json'), true);
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new EveIcon();
        }
        return self::$instance;
    }
    
    public static function getIcon($typeID) {
        EveIcon::getInstance();

        if (isset(self::$container[$typeID]) && self::$container[$typeID]['cache'] > time()) {
            return self::$container[$typeID]['file'];
        }
        return self::grabImage($typeID);
    }
    
    /*
        TypeID and Size compadible with eve's image server for Types
    */
    public static function grabImage($typeID, $size = 64){
        EveIcon::getInstance();
        $saveto = 'icons/';
        $ch = curl_init ('http://image.eveonline.com/Type/'.$typeID.'_'.$size.'.png');
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        
        $raw=curl_exec($ch);
        curl_close ($ch);
        
        $md5 = md5($raw);
        $file = $saveto.$md5.'.png';
        
        if(file_exists($file)){
            unlink($file);
        }

        
        self::$container[$typeID] = array('file'=>$file,'cache'=>time()+(60*60*24*30));

        $jsonFile  = fopen('static/type2icon.json', 'w');
        fwrite($jsonFile, json_encode(self::$container));
        fclose($jsonFile);
        
        $imageFile = fopen($file, 'x');
        fwrite($imageFile, $raw);
        fclose($imageFile);
        
        return $file;
    }
}
  
?>