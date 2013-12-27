<?php

class Query_OfferMaterials {

    function __construct($productID, $qty = 1) {
        $this->productID = $productID;
        $this->qty       = $qty;
        
        # Redis DB specifically for BPC Material caching. 
        # @todo: Set this as a singleton class?
        $this->bpcCache = new Redis();
        $this->bpcCache->connect('localhost', 6379);
        $this->bpcCache->select(Config::lpStoreRedis);
    }
  
    /*
        Execute query to obtain both BPC materials requirements and 'extra' items
        for an item. Checks cache first, which is in the format of:
        
            Class,ProductID
            
        Important to remember that it's the product ID, not the BPC ID, that 
        is stored as key.
        
        @todo: post-processing with icon path
        @return Returns array of rows
    */
    function execute() {
        $key   = get_class().','.$this->productID;
        $cache = json_decode($this->bpcCache->get($key), true);
        
        if (empty($cache) || empty($cache['manDetails']) || $cache['version'] != Db::$dbName) { 
            $cache = array('version'=>Db::$dbName, 'manDetails'=>$this->performQuery());
            $this->bpcCache->set($key, json_encode($cache));
        }
        
        # set price info for manufacturing materials
        foreach ($cache['manDetails'] AS &$manItem) {
            # this sometimes happens for some reason
            if ($manItem['totQty'] <= 0) { 
                continue; }
            
            try {
                $price = new Price(Emdr::get($manItem['typeID']));
                $manItem['price']    = $price->{Prefs::get('marketMat')}[0]; 
                $manItem['totPrice'] = $manItem['price']*$manItem['totQty']; 
            } catch (Exception $e) {
                array_push($this->noCache, $manItem['typeName']); }
        }

        return $cache['manDetails']; 
    }
    
    
    /*
        Query materials for manufacturing a product. Assumes perfect skills. 
        Included in results are qty for both single and total runs
        
        @return Returns array of rows
    */
    function performQuery() {
        $result = Db::q("
            SELECT typeID, typeName, SUM(quantity) * :quantity AS totQty, SUM(quantity) AS runQty
            FROM (
                SELECT t.typeID, t.typeName, ROUND(m.quantity * 1.1) as quantity
                FROM invTypeMaterials AS m
                INNER JOIN invTypes AS t ON m.materialTypeID = t.typeID
                WHERE m.typeID = :productID
                
                UNION

                SELECT t.typeID, t.typeName , r.quantity
                FROM ramTypeRequirements r, invTypes t, invBlueprintTypes bt, invGroups g
                WHERE 
                    r.requiredTypeID = t.typeID AND
                    r.typeID = bt.blueprintTypeID AND
                    r.activityID = 1 AND
                    g.categoryID != 16 AND 
                    t.groupID = g.groupID AND
                    bt.productTypeID = :productID 
            ) foo
            GROUP BY `typeID`", 
            array(
            ':productID' => $this->productID,
            ':quantity'  => $this->qty));
        
        return $result;
    }

}