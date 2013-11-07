<?php

# @todo: fix this whole file.

class DB extends PDO {
    /**
     * Count how many times the e/ea functions are used
     */
    var $qs = array();

    /**
     * We do our own connection stuff using a config file array.
     * See the example files in the config/ directory for more info.
     * Only driver invocation is supported
     */
    function __construct(array $dsn_cfg, array $info, bool $debug = null) {
        $dsn = $dsn_cfg['driver'].':'.http_build_query($dsn_cfg['dsn_opts'], '', ';');
        
        parent::__construct($dsn, $info['uname'], $info['passwd'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                #PDO::ATTR_PERSISTENT => true
        ));
        
        if ( isset($dsn_cfg['schema']) ) {
            $this->query('SET search_path = '.$dsn_cfg['schema']);
        }

        unset($info); # unset authentication details
    }
    /**
     * Queries without the suck.
     * First parameter is the query, anything after that are taken as SQL parameters.
     * @return PDOStatement Executed statement handle
     */
    function e() {
        $start = microtime(true);
        // Get input, first function parameter is the SQL query, all the rest are SQL parameters
        $params = func_get_args();
        assert('count($params) >= 2');

        $query = array_shift($params);
        $prep = $this->prepare($query);
        $prep->execute($params);
        
        $end = microtime(true);
        
        //$ex_prep = $this->prepare("EXPLAIN ".$query);
        //$ex_prep->execute($params);
          
        $time = sprintf('%01.002fms (%0.5fs)', ($end - $start) * 1000, $end - $start);

        $this->qs[] = array($query, $params, $time);
        return $prep;
    }

    /**
     * Exec-array - like mysql_query but safe.
     * @param $query string SQL query to execute
     * @param $params array Values to fill the ?s in the SQL with
     * @return PDOStatement Executed statement handle
     */
    function ea($query, array $params) {
        $start = microtime(true);

        $prep = $this->prepare($query);
        $prep->execute($params) || error_log(sprintf('Execution of "%s" failed', $query));
        $end = microtime(true);
          
       // $ex_prep = $this->prepare("EXPLAIN ".$query);
       // $ex_prep->execute($params)  || error_log(sprintf('Execution of "%s" failed', $query));
         
        $time = sprintf('%01.002fms (%0.5fs)', ($end - $start) * 1000, $end - $start);
        
        $this->qs[] = array($query, $params, $time);
        return $prep;
    }

    /**
     * Query - Shorthand for execute-fetch1row.
     * @param $query string SQL query
     * @param $params mixed Either a scalar or array of them to use as query parameters
     * @param $fetch_style mixed Passed directly to PDOStatement::fetch.
     * @return mixed Return value from fetching a row. Default $fetch_style is an assoc array.
     */
    function q($query, $params, $fetch_style = PDO::FETCH_NUM) {
        if ( is_scalar($params) ) {
            return $this->e($query, $params)->fetch($fetch_style);
        }
        else {
            return $this->ea($query, $params)->fetch($fetch_style);
        }
    }

    /**
     * Shorthand for execute-fetch1stcolumn
     * @param $query string SQL query
     * @param $params mixed Either a scalar or array of them to use as query parameters
     * @return mixed Contents of the first column of the result
     */
    function q1($query, $params) {
        if ( is_scalar($params) ) {
            return $this->e($query, $params)->fetchColumn();
        }
        else {
            return $this->ea($query, $params)->fetchColumn();
        }
    }
    
    /**
     * Added by Ryan H. - this was a quick hack to get something working.
     *
     * Fetch all rows returned from database.
     * @param $query string SQL query
     * @param $params mixed Either a scalar or array of them to use as query parameters
     * @param $fetch_style mixed Passed directly to PDOStatement::fetch.
     * @return mixed Return value from fetching a row. Default $fetch_style is an assoc array.
     */
    function qa($query, $params, $fetch_style = PDO::FETCH_ASSOC) {
        if ( is_scalar($params) ) {
            return $this->e($query, $params)->fetchAll($fetch_style);
        }
        else {
            return $this->ea($query, $params)->fetchAll($fetch_style);
        }
    }
    
    /**
     * Added by Ryan H. - function is dead, hopefully I can get it working...
     *
     * Explains query. This was going to be used in debug and be added to the $qs array.
     * @param $query string SQL query
     * @param $params mixed Either a scalar or array of them to use as query parameters
     * @param $fetch_style mixed Passed directly to PDOStatement::fetch.
     * @return mixed Return value from fetching a row. Default $fetch_style is an assoc array.
     */
    function explain($query, $params, $fetch_style = PDO::FETCH_ASSOC) {
        if ( is_scalar($params) ) {
            $params = func_get_args();
            assert('count($params) >= 2');
            $query = array_shift($params);
            $prep = $this->prepare("EXPLAIN ".$query);
            return $prep->execute($params)->fetchAll($fetch_style);
        }
        else {
            $prep = $this->prepare("EXPLAIN ".$query);
            return $prep->execute($params)->fetchAll($fetch_style) || error_log(sprintf('Execution of "%s" failed', $query));
        }
    }

    /**
     * Syntactic sugar - calls SQL functions as if they were class methods.
     * Requires > PHP 5.2.5; see php bug #43663
     */
    function __call($funcname, array $params) {
        return $this->ea('SELECT '.$funcname.'('.str_repeat('?,', count($params)-1).'?)', $params);
    }
}
