<?php

/* zKillboard
 * Copyright (C) 2012-2013 EVE-KILL Team and EVSCO.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Redis Cache Class
 * 
 * @use Redis (https://github.com/nicolasff/phpredis)
 *
 */
class RedisCache {
    /**
     * Redis object instance
     *
     * @var object Redis object
     */
    private $_redis;

    /**
     * Instantiates the `Redis` object and connects it to the configured server.
     *
     * @return void
     */
    function __construct() {

        if (!$this->_redis) {
            $this->_redis = new Redis(); }
        
        /* 
            @todo: every so often, this error pops up.
            Look into why. If no fix available, display error page
        */
        try {
            $this->_redis->connect('localhost', 6379, 0);
            $this->_redis->select(Config::emdrRedis);
        } catch (RedisException $e) {
            echo 'Opps... Something happened. Please try refreshing page.<br /><br /> Error message: ',  $e->getMessage();
            die();
        }
        /*
        if (substr($redisServer, 0, 7) == "unix://") {
            $this->_redis->pconnect(substr($redisServer, 7), 0.0, null, 0);
        } else {
            $this->_redis->pconnect($redisServer, $redisPort, 0.0, null, 0);
        }
        */
    }

    /**
     * Sets expiration time for cache key
     *
     * @param string $key The key to uniquely identify the cached item
     * @param mixed $timeout A `strtotime()`-compatible string or a Unix timestamp.
     * @return boolean
     */
    protected function _expireAt($key, $timeout)
    {
        return $this->_redis->expireAt($key, is_int($timeout) ? $timeout : strtotime($timeout));
    }

    /**
     * Read value from the cache
     *
     * @param string $key The key to uniquely identify the cached item
     * @return mixed
     */
    public function get($key)
    {
        return $this->_redis->get($key);
    }

    /**
     * Write value to the cache
     *
     * @param string $key The key to uniquely identify the cached item
     * @param mixed $value The value to be cached
     * @param null|string $timeout A strtotime() compatible cache time.
     * @return boolean
     */
    public function set($key, $value, $timeout)
    {
        $result = $this->_redis->set($key, $value);
        return $result ? $this->_expireAt($key, $timeout) : $result;
    }

    /**
     * Override value in the cache
     *
     * @param string $key The key to uniquely identify the cached item
     * @param mixed $value The value to be cached
     * @param null|string $timeout A strtotime() compatible cache time.
     * @return boolean
     */
    public function replace($key, $value, $timeout)
    {
        return  $this->_redis->set($key, $value, $timeout);
    }

    /**
     * Delete value from the cache
     *
     * @param string $key The key to uniquely identify the cached item
     */
    public function delete($key)
    {
        return (boolean) $this->_redis->del($key);
    }

    /**
     * Performs an atomic increment operation on specified numeric cache item.
     *
     * Note that if the value of the specified key is *not* an integer, the increment
     * operation will have no effect whatsoever. Redis chooses to not typecast values
     * to integers when performing an atomic increment operation.
     *
     * @param string $key Key of numeric cache item to increment
     * @param integer $offset Offset to increment - defaults to 1
     * @return Closure Function returning item's new value on successful increment, else `false`
     */
    public function increment($key, $step = 1, $timeout = 0)
    {
        if ($timeout) {
            $this->_expireAt($key, $timeout);
        }
        return $this->_redis->incr($key, $step);
    }

    /**
     * Performs an atomic decrement operation on specified numeric cache item.
     *
     * Note that if the value of the specified key is *not* an integer, the decrement
     * operation will have no effect whatsoever. Redis chooses to not typecast values
     * to integers when performing an atomic decrement operation.
     *
     * @param string $key Key of numeric cache item to decrement
     * @param integer $step Offset to decrement - defaults to 1
     * @param string $timeout A strtotime() compatible cache time.
     * @return Closure Function returning item's new value on successful decrement, else `false`
     */
    public function decrement($key, $step = 1, $timeout = 0)
    {
        if ($timeout) {
            $this->_expireAt($key, $timeout);
        }
        return $this->_redis->decr($key, $step);
    }

    /**
     * Clears user-space cache
     *
     * @return boolean
     */
    public function flush()
    {
        $this->_redis->flushdb();
    }

}
