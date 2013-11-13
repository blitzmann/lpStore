<?php

abstract class Config {
 
    const emdrRedis    = 0; # database for emdr-py
    const lpStoreRedis = 1; # database for lpStore
    const dsnDetails   = '/home/http/private/db-eve-latest.ini';
    const authDetails  = '/home/http/private/auth-eve.ini';
    const emdrVersion  = 1;
    
    static $themes = array('clean');

    static public function getDbDsn() {
        return parse_ini_file('/home/http/private/db-eve-latest.ini', true); }
        
    static public function getDbAuth() {
        return parse_ini_file('/home/http/private/auth-eve.ini'); }
}