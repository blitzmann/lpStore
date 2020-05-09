<?php

class Query_CorpStations {

    function __construct($corpID, $system = null) {
        $this->corpID = $corpID;
        $this->system = $system;
    }

    /*
        Execute query to obtain all Stations for a corp

        @return Returns array of rows
    */
    function execute() {
        $result = Db::q("
                SELECT SQL_NO_CACHE a.`stationName`, a.`stationID`, b.`security`, b.`solarSystemID`, c.`regionName`,
                (
                    SELECT seq
                    FROM graphJumps
                    WHERE latch = 'breadth_first'
                    AND origid = :origin
                    AND destid = b.`solarSystemID`
                    ORDER BY seq DESC
                    LIMIT 0,1
                ) AS jumps
                FROM `staStations` a
                INNER JOIN `mapSolarSystems` b ON (b.`solarSystemID` = a.`solarSystemID`)
                INNER JOIN `mapRegions` c ON (a.`regionID` = c.`regionID`)
                WHERE a.`corporationID` = :corpID
                ORDER BY a.`stationName` ASC",
                array(':corpID'=>$this->corpID, ':origin'=>$this->system));

        return $result;
    }

    # Returns list of stations and their jump distance in JSON format (stationID=>Jumps)
    function json() {
        $result = $this->execute();

        $json = array();
        foreach ($result AS $s) {
            $json[$s['stationID']] = $s['jumps'];
        }
        return json_encode($json);
    }
}