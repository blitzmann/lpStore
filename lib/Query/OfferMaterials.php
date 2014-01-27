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
        
            Class:ProductID,Qty

        Important to remember that it's the product ID, not the BPC ID, that 
        is stored as key.

        @todo: post-processing with icon path
        @return Returns array of rows
    */
    function execute() {
        $key   = get_class().':'.$this->productID.','.$this->qty;
        $cache = json_decode($this->bpcCache->get($key), true);
        
        if (empty($cache) || empty($cache['manDetails']) || $cache['version'] != Db::$dbName) { 
            $cache = array('version'=>Db::$dbName, 'manDetails'=>$this->performQuery());
            $this->bpcCache->set($key, json_encode($cache));
        }

        # set price info for manufacturing materials
        foreach ($cache['manDetails'] AS &$manItem) {
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
        
        Blueprint are finicky things. There are some blueprints that pop up as having
        material that are needed, only to be negated by another query. I think
        I originally got this query from Fuzzy's Steve

        @todo: look into making this not suck so bad

        @return Returns array of rows
    */

    function performQuery() {
        $result1 = Db::q('
            SELECT t.typeID,
                   t.typeName,
                   ROUND(greatest(0,sum(t.quantity)) * (1 + (b.wasteFactor / 100))) AS runQty
            FROM
              (SELECT invTypes.typeid typeID,
                      invTypes.typeName typeName,
                      quantity
               FROM invTypes,
                    invTypeMaterials,
                    invBlueprintTypes
               WHERE invTypeMaterials.materialTypeID=invTypes.typeID
                AND invBlueprintTypes.productTypeID = invTypeMaterials.typeID

                 AND invTypeMaterials.TypeID=:productID
               UNION 
               SELECT invTypes.typeid typeid,
                            invTypes.typeName name,
                            invTypeMaterials.quantity*r.quantity*-1 quantity
               FROM invTypes,
                    invTypeMaterials,
                    ramTypeRequirements r,
                    invBlueprintTypes bt
               WHERE invTypeMaterials.materialTypeID=invTypes.typeID
                 AND invTypeMaterials.TypeID =r.requiredTypeID
                 AND r.typeID = bt.blueprintTypeID
                 AND r.activityID = 1
                 AND bt.productTypeID=:productID
                 AND r.recycle=1) t
            INNER JOIN invBlueprintTypes b ON (b.productTypeID = :productID)

            GROUP BY t.typeid,
                     t.typeName', 
            array(':productID' => $this->productID));
        // Get extra items needed
        $result2 = Db::q('
            SELECT t.typeID AS    typeID,
                t.typeName AS     typeName,
                r.quantity AS runQty
            FROM ramTypeRequirements r,
                invTypes t,
                invBlueprintTypes bt,
                invGroups g
            WHERE r.requiredTypeID = t.typeID
            AND r.typeID = bt.blueprintTypeID
            AND r.activityID = 1
            AND bt.productTypeID = :productID
            AND g.categoryID != 16
            AND t.groupID = g.groupID',
            array(':productID' => $this->productID));
        
        $mats = array();
        foreach($result = array_merge($result1, $result2) AS &$item) {
            # this sometimes happens for some reason

            if ($item['runQty'] <= 0) { 
              $item = null; continue;}

            if (!isset($mats[$item['typeID']])) { $mats[$item['typeID']] = $item; $mats[$item['typeID']]['totQty'] = 0; }
            
            $mats[$item['typeID']]['totQty'] += $item['runQty'] * $this->qty;
        }

        return array_filter($mats);
    }

    /*
        Old SQL that doesn't negate materials

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
            GROUP BY `typeID`
    */
    
}