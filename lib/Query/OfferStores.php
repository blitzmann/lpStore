<?php

class Query_OfferStores {

    function __construct($offerID) {
        $this->offerID = $offerID;
    }
  
    /*
        Execute query to obtain all Stores for an offer
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q('
            SELECT s.*, u.`itemName` 
            FROM `lpStore` s 
            INNER JOIN `invUniqueNames` u ON (u.`itemID` = s.`corporationID`) 
            WHERE `offerID` = :offerID', 
            array(':offerID'=>$this->offerID));
        
        return $result;
    }

}