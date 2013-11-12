<?php

abstract class Config {
 
    const emdrRedis    = 0; # database for emdr-py
    const lpStoreRedis = 1; # database for lpStore
    
    static $themes = array('clean');

}