<?php

class Query_CorpReqItems {

    function __construct($corpID) {
        $this->corpID = $corpID;
    }
  
    /*
        Execute query to obtain all required items for the store
        
        @return Returns typeID-indexed array containing item info
    */
    function execute() {
        $container = array();
        $result = Db::q('
            SELECT a.typeID, a.quantity, b.typeName, a.offerID
            FROM lpOfferRequirements a
            NATURAL JOIN invTypes b
            NATURAL JOIN lpStore c
            WHERE c.corporationID = :corpID
            ORDER BY a.quantity ASC',
            array(':corpID' => $this->corpID));
        
        # @todo: add icon path for items
        foreach ($result AS $item) {
            $container[$item['offerID']][] = $item;
        }
        
        return $container;
    }

}