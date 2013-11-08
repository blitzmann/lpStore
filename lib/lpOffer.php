<?php

/*
    Class can either be initialized as a stand alone object, 
    in which case $DB must be passed and the class will pull 
    the data from the DB and calculate offer details, or it 
    can be initialized via lpStore in which case lpStore will 
    supply details for the offer and lpOffer simply calculates
    prices.
    
    The reason for this is to cut down on the number of queries. 
    If we initialized lpOffer() by itself for every LP Store 
    offer, that's potentially hundreds of queries just to get 
    the info for the entire LP Store. lpStore() will do one big 
    query to get all offers and 1 query to get required items, 
    and pass this info along to lpOffer()
    
    @todo: allow switching req and man costs between sell and buy?
*/

class lpOffer {
    
    private $DB;
    private $emdr;
    
    ## Details from database
    public $offerDetails;
    public $reqDetails; # null = standalone, array() = no items via lpStore
    
    ## Properties of the offer itself
    public $offerID;
    public $cached   = false; # was pricing info cached for offer?
    public $bpc      = false; # is offer a BPC?
    public $price    = null;  # pricing info for item (if blueprint, pricing info of manufactured item)
    public $timeDiff = null;  # between now and when price was cached
    public $totalCost = 0;    # total cost of offer (init isk/req/man)
    public $profit    = 0;    # profit of offer after subtraction total cost
    public $lp2isk    = 0;    # isk/lp after all calculations
    
    ## Details for manufacturing
    public $manDetails = array();
    
    public $noCache  = array(); # store typeID => name of items with no price cache
    
    /*
        Class is initiated with very basic information. Absolutely no calculation 
        is done at this point. This is becasue class may be initialized by itself 
        or as part of lpStore, and lpStore injects offer details before calcs 
        take place.
        
        lpOffer isn't meant to return anything, but rather collect info and store 
        in class properties for access.        
    */
    public function __construct($offerID, $emdr, $DB = null) {
        $this->DB      = $DB;
        $this->emdr    = $emdr;
        $this->offerID = $offerID;
    }
    
    /*
        calc() takes care of initializing calculation functions
        It is also responsible for gathering data for items if needed.
    */
    # use $mode to determine if it's sell/buy
    public function calc($mode) {  
        /*
        if ($mode !== 'sell' || $mode !== 'buy') {
            # @todo: do actual error
            die('HORRIBLE ERROR: Market Mode not valid. Plz fix');
        }
        */
        if ($this->offerDetails === null) {
            $this->offerDetails = $this->DB->qa($this->sql['oDetails'], array($this->offerID))[0]; }
            
        if ($this->reqDetails === null) {
            $this->reqDetails = $this->DB->qa($this->sql['rDetails'], array($this->offerID)); }
        
        if (strstr($this->offerDetails['typeName'], " Blueprint")) {
            # If this is a bpc, set the flag and run bpc function
            $this->bpc = true; 
            $this->bpc();
        } else {
            # if this is not a blueprint, go ahead and set price via given known typeID
            try {
                $price = new Price($this->emdr->get($this->offerDetails['typeID']));
                $this->cached      = true;
                $this->price       = $price->sell[0];
                $this->totalVolume = $price->sell[1];
                $this->timeDiff    = (time() - $price->generatedAt)/60/60; # time difference in hours
            } catch (Exception $e) {
                array_push($this->noCache, $this->offerDetails['typeName']); }
        }
        
        foreach ($this->reqDetails AS &$reqItem) {
            try {
                $price = new Price($this->emdr->get($reqItem['typeID']));
                $reqItem['price'] = $price->sell[0]; 
            } catch (Exception $e) {
                array_push($this->noCache, $reqItem['typeName']); }
        }
        
        # calculate total cost
        $this->totalCost = $this->offerDetails['iskCost'];
        foreach ($this->reqDetails AS &$reqItem) {
            $this->totalCost += ($reqItem['quantity'] * $reqItem['price']); }
            
        foreach ($this->manDetails AS &$manItem) {
            $this->totalCost += ($manItem['quantity'] * $manItem['price']); }
        
        # calculate profits / isk/lp
        $this->profit = ($this->price * $this->offerDetails['quantity'] - $this->totalCost);
        $this->lp2isk = $this->profit / $this->offerDetails['lpCost']; 
    }

    private function totalCost() {
        $this->totalCost = $this->offerDetails['iskCost'];
        foreach ($this->reqDetails AS &$reqItem) {
            $this->totalCost += ($reqItem['quantity'] * $reqItem['price']); }
            
        foreach ($this->manDetails AS &$manItem) {
            $this->totalCost += ($manItem['quantity'] * $manItem['price']); }
    }
    
    /*# holder for old code.
    function FIX(){
            $totalCost = $offer['iskCost'];

            $manReqItems = array();

               
            else {
                $name = $offer['quantity']." x ".$offer['typeName']; }
               
            if (!$cached) {
                $lp2isk = 'N/A';
                $profit = 0; }
            else {
                $profit = ($price['orders'][$marketMode][0]*$offer['quantity'] - $totalCost);
                $lp2isk = $profit / $offer['lpCost']; 
            }
    }
    */
    /*
        bpc() finds the pricing info for the manufactured item, which lpOffer()
        will use to calculate isk/lp. it also sets required building materials
    */
    private function bpc() {
        if (!$this->bpc) { return; } # Something's gone wrong, don't do this if not a BPC
        
        # Do this in template
        // $name =  "1 x ".$offer['typeName']." Copy (".$offer['quantity']." run".($offer['quantity'] > 1 ? "s" : null).")"; 

        $manTypeID = $DB->q1($this->sql['manTypeID'], array($offer['typeID']));
        
        // set pricing info as the manufactured item
        if ($price = $emdr->get($manTypeID)) {
            $price  = json_decode($price, true); 
            $timeDiff = (time() - $price['orders']['generatedAt'])/60/60; // time difference in hours
            $cached = true;
        }
        
        // Here we merge bill of materials for blueprints (remembering to multiple qnt with # of BPC runs)
        $manReqItems = array_merge(
            // Get minerals needed
            $DB->qa($this->sql['manMinerals'], array($offer['quantity'], $manTypeID, $manTypeID, $manTypeID)),
            // Get extra items needed
            $DB->qa($this->sql['manExtra'], array($offer['quantity'], $manTypeID))); // append material needs to req items      
        
        foreach ($manReqItems AS $reqItem) {
            if ($reqItem['quantity'] <= 0) {
                continue; }

            if ($rprice = $emdr->get($reqItem['typeID'])) {
                $rprice = json_decode($rprice, true);
                $totalCost = $totalCost + ($rprice['orders']['sell'][0] * $reqItem['quantity']);
            }
        }
        // one day this will display them all, but for now, just note that materials are needed...
        array_push($req, "Manufacturing Materials");
  
    }
    
    /*
        Having SQL throughout the class is ugly as shit. 
        @todo: look into possibly setting up class full of SQL const and call from SQL::QUERYNAME
    */
    private $sql = array (
        'oDetails'=>'SELECT a.*, b.`typeName` FROM `lpOffers` a NATURAl JOIN `invTypes` b WHERE `offerID` = ? LIMIT 0,1',
        'rDetails'=>'SELECT a.*, b.typeName FROM lpOfferRequirements a NATURAL JOIN invTypes b WHERE `offerID` = ?',
        'manTypeID'=>'SELECT `ProductTypeID` FROM `invBlueprintTypes` WHERE `blueprintTypeID` = ?',
        
        # I don't remember where I stole this from. It's one hell of a query tho
        'manMinerals'=><<<'SQL'
SELECT t.typeID, t.typeName, ROUND(greatest(0,sum(t.quantity)) * (1 + (b.wasteFactor / 100))) * ? AS quantity
FROM
   (SELECT invTypes.typeid typeID, invTypes.typeName typeName, quantity 
    FROM invTypes, invTypeMaterials, invBlueprintTypes
    WHERE invTypeMaterials.materialTypeID = invTypes.typeID AND
          invBlueprintTypes.productTypeID = invTypeMaterials.typeID AND
          invTypeMaterials.TypeID = ?
    UNION 
    SELECT invTypes.typeid typeid, invTypes.typeName name, invTypeMaterials.quantity * r.quantity * - 1 quantity
    FROM invTypes, invTypeMaterials, ramTypeRequirements r, invBlueprintTypes bt 
    WHERE invTypeMaterials.materialTypeID=invTypes.typeID AND
          invTypeMaterials.TypeID =r.requiredTypeID AND
          r.typeID = bt.blueprintTypeID AND
          r.activityID = 1 AND 
          bt.productTypeID = ? AND 
          r.recycle = 1
   ) t
INNER JOIN invBlueprintTypes b ON (b.productTypeID = ?)
GROUP BY t.typeid, t.typeName
SQL
,       'manExtra'=><<<'SQL'
SELECT t.typeID AS typeID,  t.typeName AS     typeName,
(r.quantity * ?) AS quantity
FROM ramTypeRequirements r,
invTypes t,
invBlueprintTypes bt,
invGroups g
WHERE r.requiredTypeID = t.typeID
AND r.typeID = bt.blueprintTypeID
AND r.activityID = 1
AND bt.productTypeID = ?
AND g.categoryID != 16
AND t.groupID = g.groupID
SQL
    );
 
}

?>