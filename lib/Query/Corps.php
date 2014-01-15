<?php

class Query_Corps {

    function __construct($search = null) { 
        $this->search = '%'.$search.'%';
    }
    
    /*
        Execute query to obtain all corps
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q('
            SELECT a.corporationID, b.itemName AS corpName
            FROM lpStore a 
            INNER JOIN invUniqueNames b ON (a.corporationID = b.itemID AND b.groupID = 2) 
            WHERE b.itemName LIKE :query
            GROUP BY a.corporationID',
            array(':query' => $this->search)
        );
        
        return $result;
    }
    
    # Returns list of corps in JSON typeahead format
    function json() {
        $result = $this->execute();
        
        $json = array();
        foreach ($result AS $corp) {
            $json[] = array(
                'id'   => $corp['corporationID'],
                'value' => $corp['corpName'],
                'type' => 'corporation'
            );
        }
        return json_encode($json);
    }

}