<?php

class EveIcon {

    private static $instance = null;
    private static $container = array();

    const TYPE2ICON_FILE = 'static/type2icon.json';
    const ICONS_DIR      = 'icons/';
    const NAVY_META      = '73_16_246';
    const T2_META        = '73_16_242';
    
    protected function __construct() {
        self::$container = json_decode(file_get_contents(self::TYPE2ICON_FILE), true);
        //echo "Doing container stuff";
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new EveIcon();
        }
        return self::$instance;
    }
    
    public static function getIcon($typeID, $size = 64, $mod = false) {
        EveIcon::getInstance();
        $iconID = self::$container[$typeID];
        $things = split('_', $iconID);
        //var_dump($typeID);
        //var_dump($things[0].'_64_'.$things[1]);
        $t2   = imagecreatefrompng(EveIcon::ICONS_DIR . EveIcon::T2_META   . '.png');
        $navy = imagecreatefrompng(EveIcon::ICONS_DIR . EveIcon::NAVY_META . '.png');
        $base = imagecreatefrompng(EveIcon::ICONS_DIR . $things[0].'_64_'.$things[1] . '.png');

        imagesavealpha($base, true);
        //imagecopyresampled($base, $navy, 0, 0, 0, 0, 16, 16, 16, 16);
        return imagepng($base);
    }
}
  
?>  
