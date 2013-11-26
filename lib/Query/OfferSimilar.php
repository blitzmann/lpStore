<?php

class Query_OfferSimilar {

    function __construct($typeID) {
        $this->typeID = $typeID;
    }
  
    /*
        Execute query to obtain all similar offers for an offer
        A similar offer is one that has the same typeID (differnt requirements)
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q('
            SELECT `lpOffers`.*, `invTypes`.`typeName` 
            FROM `lpOffers` 
            NATURAL JOIN `lpStore` 
            NATURAL JOIN `invTypes` 
            WHERE `typeID` = :typeID GROUP BY `offerID`', 
            array(':typeID'=>$this->typeID));
        
        return $result;
    }

}