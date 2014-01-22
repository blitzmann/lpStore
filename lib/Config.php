<?php

abstract class Config {
 
    const emdrRedis    = 0; # database for emdr-py
    const lpStoreRedis = 1; # database for lpStore
    const dsnDetails   = '/home/http/private/db-eve-latest.ini';
    const authDetails  = '/home/http/private/auth-eve.ini';
    const emdrVersion  = 1;
    
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
        return parse_ini_file('/home/http/private/db-eve-latest.ini', true); }
        
    static public function getDbAuth() {
        return parse_ini_file('/home/http/private/auth-eve.ini'); }
}