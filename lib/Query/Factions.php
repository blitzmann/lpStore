<?php

class Query_Factions {

    function __construct() { }
  
    /*
        Execute query to obtain all factions
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q('
            SELECT b.factionID, f.itemName AS factionName
            FROM `lpStore` a 
            INNER JOIN crpNPCCorporations b ON (b.corporationID = a.corporationID) 
            INNER JOIN invUniqueNames f ON (b.factionID = f.itemID)
            GROUP BY b.factionID
            ORDER BY b.factionID ASC' 
        );
        
        return $result;
    }

}