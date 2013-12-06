<?php

class Query_CorpOffers {

    function __construct($corpID) {
        $this->corpID = $corpID;
    }
  
    /*
        Execute query to obtain all LP Store offers
        
        @return Returns Db::q array of rows
    */
    function execute() {
        $results = Db::q('
            SELECT `lpStore`.* , `invTypes`.`typeName`, `lpOffers`.*, 
                (
                    SELECT linkid 
                    FROM graphMarket
                    WHERE latch = 2
                    AND origid = 1
                    AND seq = 1
                    AND destid = `invTypes`.`marketGroupID`
                ) AS marketRoot
            FROM lpStore
            NATURAL JOIN lpOffers
            NATURAL JOIN invTypes
            WHERE `lpStore`.`corporationID` = :corpID
            ORDER BY 
                `lpOffers`.`lpCost`, 
                `lpOffers`.iskCost, 
                `invTypes`.`typeName`', 
            array(':corpID' => $this->corpID));
        
        foreach ($results AS &$result) {
            // $result['icon'] = EveIcon::getIcon($result['typeID']); 
        }
            
        return $results;
    }

}