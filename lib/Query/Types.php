<?php

class Query_Types {

    function __construct($search = null) { 
        $this->search = '%'.$search.'%';
    }
  
    /*
        Execute query to obtain all types in LP Store (with WHERE clause)
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q("
            SELECT a.typeID, b.typeName
            FROM lpOffers a 
            INNER JOIN invTypes b ON (a.typeID = b.typeID) 
            WHERE typeName LIKE :query
            GROUP BY typeID
            LIMIT 0,30",
            array(':query' => $this->search)
        );
        
        return $result;
    }
    
    # Returns list of types in JSON typeahead format
    function json() {
        $result = $this->execute();
        
        $json = array();
        foreach ($result AS $offer) {
            $json[] = array(
                'id'   => $offer['typeID'],
                'value' => $offer['typeName'],
                'type' => 'item'
            );
        }
        return json_encode($json);
    }

}