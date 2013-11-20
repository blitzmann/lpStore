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
        $result = Db::q('
            SELECT `lpStore`.* , `invTypes`.`typeName`, `lpOffers`.*
            FROM lpStore
            NATURAL JOIN lpOffers
            NATURAL JOIN invTypes
            WHERE `lpStore`.`corporationID` = :corpID
            ORDER BY 
                `lpOffers`.`lpCost`, 
                `lpOffers`.iskCost, 
                `invTypes`.`typeName`', 
            array(':corpID' => $this->corpID));
        
        return $result;
    }

}