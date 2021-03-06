<?php

abstract class Config {

    const emdrRedis    = 1; # database for emdr-py
    const lpStoreRedis = 2; # database for lpStore (int or False)
    const emdrVersion  = 1;

    # config files. Samples included in inc/ directory
    const dsnDetails   = '../private/db-eve-latest.ini';
    const authDetails  = '../private/auth-eve.ini';

    static $themes = array('clean');

    static $secColors = array(
        'FF0000',//0
        'E43300',//1
        'FD4C00',//2
        'F56607',//3
        'D27606',//4
        'BABD13',//5
        '79C62E',//6
        '06CA07',//7
        '05D444',//8
        '28C296',//9
        '06D6D6');

    static public function getDbDsn() {
        return parse_ini_file(self::dsnDetails, true); }

    static public function getDbAuth() {
        return parse_ini_file(self::authDetails); }
}