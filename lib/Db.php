<?php

/*
    Handle DB connection and queries
    Lots of inspiration from zKillboard <https://github.com/EVE-KILL/zKillboard>,
    specifically introducing me to the singleton concept.
*/
class Db {
    
    protected static $instance = null;

    private $pdo = null;
    
    public static $dbName     = null;
    public static $queryCount = 0;

    public static function getInstance() {
        if (Db::$instance == null) {
            Db::$instance = new Db(); }
        return Db::$instance;
    }
    
    /**
     * Creates and returns a PDO object.
     *
     * @static
     * @return PDO
     */
    protected static function getConn() {
        if (Db::getInstance()->pdo != null) {
            return Db::getInstance()->pdo; }
        
        $dsnCfg = Config::getDbDsn();
        $dbAuth = Config::getDbAuth();
        
        $dsn = $dsnCfg['driver'].':'.http_build_query($dsnCfg['dsn_opts'], '', ';');
        self::$dbName = $dsnCfg['dsn_opts']['dbname'];
        
        try {
            Db::getInstance()->pdo = new PDO($dsn, $dbAuth['uname'], $dbAuth['passwd'], array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (Exception $e) {
            die($e->getMessage());
        }
        # Don't want this floating around all willy nilly
        $dbAuth = null;

        return Db::getInstance()->pdo;
    }
    
    /**
     * @static Prepares PDO statement
     * @param $query string SQL query
     * @param $params array (optional) A key/value array of parameters
     * @return Returns PDOStatement.
     */
    public static function p($query, $params = array()) {
        self::$queryCount += 1;
        if (strpos($query, ";") !== false) {
            throw new Exception("Semicolons are not allowed in queries.  Use parameters instead."); }
        
        $conn = self::getConn();
        $prep = $conn->prepare($query);
        $prep->execute($params);

        return $prep;
    }

    
    /**
     * @static Fetch all
     * @param $query string SQL query
     * @param $params array (optional) A key/value array of parameters
     * @param $fetch int (optional) Passed directly to PDOStatement::fetchAll.
     * @return Return value from fetching a row. Returns empty array if no rows.
     */
    public static function q($query, $params = array(), $fetch = PDO::FETCH_ASSOC) {
        return self::p($query, $params)->fetchAll($fetch);
    }

    /**
     * @static Query Row
     * @param $query string SQL query
     * @param $parameters array (optional) A key/value array of parameters
     * @param $fetch int Passed directly to PDOStatement::fetch.
     * @return Returns the first row of the result set. Returns null if row does not exist.
     */
    public static function qRow($query, $params = array(), $fetch = PDO::FETCH_ASSOC) {
        $result = self::p($query, $params)->fetch($fetch);
        if (!empty($result)){
            return $result; }
        return null;
    }
    
    /**
     * @static Query column
     * @param $query string SQL query
     * @param $params array (optional) A key/value array of parameters.
     * @param $column mixed The 0-indexed column of result or the name of the field to return
     * @return Returns the value of $column in the first row of the resultset. Returns null if there is no column/row.
     */
    public static function qColumn($query, $params = array(), $column = 0) {
        if (is_int($column)) {
            $result = self::p($query, $params)->fetchColumn($column); 
            if (!$result) { return null; }
            return $result;
        }
        else {
            $result = self::qRow($query, $params); 
            if (!$result || !isset($result[$column])) { return null; }
            return $result[$column];
        }
    }

    public static function getLpDbVersion() {
        # not exactly sure why this is required; without it, on pages with no 
        # queries besides this one, the proper result is not returned
        self::getConn();
        return self::qColumn("
            SELECT table_comment
            FROM INFORMATION_SCHEMA.TABLES
            WHERE table_schema = :dbName
            AND table_name =  :tblName",
            array(':dbName' => self::$dbName, ':tblName' => 'lpStore'));
    }
    
    public static function getDbName() {
        self::getConn(); # init's PDO and thus sets DB Name property of class
        return self::$dbName;
    }
    
    protected function __clone() { }
}
