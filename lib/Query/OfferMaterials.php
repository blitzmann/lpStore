<?php

class Query_OfferMaterials {

    function __construct($typeID, $qty = 1) {
        $this->typeID = $typeID;
        $this->qty    = $qty;

        # Redis DB specifically for BPC Material caching.
        # @todo: Set this as a singleton class?
        $this->bpcCache = new Redis();
        $this->bpcCache->connect('localhost', 6379);
        $this->bpcCache->select(Config::lpStoreRedis);
    }

    /*
        Execute query to obtain both BPC materials requirements and 'extra' items
        for an item. Checks cache first, which is in the format of:

            Class:bpcTypeID,Qty

        @todo: post-processing with icon path
        @return Returns array of rows
    */
    function execute() {
        $key   = get_class().':'.$this->typeID.','.$this->qty;
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

        @return Returns array of rows
    */

    function performQuery() {
        $result = Db::q('
            SELECT a.materialTypeID as typeID, b.typeName, a.quantity as runQty, a.quantity*:bpcRuns AS totQty
            FROM industryActivityMaterials a
            JOIN invTypes b on (a.materialTypeID = b.typeID)
            WHERE a.typeID=:typeID',
            array(':typeID' => $this->typeID, ':bpcRuns'=>$this->qty));

        return $result;
    }
}