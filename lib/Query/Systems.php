<?php

class Query_Systems {

    function __construct($search = null) { 
        $this->search = '%'.$search.'%';
    }
  
    /*
        Execute query to obtain all systems (with WHERE clause)
        
        @return Returns array of rows
    */
    function execute() {
        $result = Db::q("
            SELECT solarSystemID, solarSystemName
            FROM mapSolarSystems
            WHERE solarSystemName LIKE :query
            ORDER BY solarSystemName ASC
            LIMIT 0,30",
            array(':query' => $this->search)
        );
        
        return $result;
    }
    
    # Returns list of types in JSON typeahead format
    function json() {
        $result = $this->execute();
        
        $json = array();
        foreach ($result AS $sys) {
            $json[] = array(
                'id'   => $sys['solarSystemID'],
                'value' => $sys['solarSystemName'],
            );
        }
        return json_encode($json);
    }

}