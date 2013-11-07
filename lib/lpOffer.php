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
*/

class lpOffer {
    
    public $offerDetails;
    public $reqDetails; # null = standalone, array() = no items via lpStore
    
    public $offerID;
    public $cached = false; # was pricing info cached for offer?
    public $bpc    = false; # is offer a BPC?

    private $DB;
    private $emdr;
    
    # init's class, setting barebone variables
    function __construct($offerID, $emdr, $DB = null) {
        $this->DB      = $DB;
        $this->emdr    = $emdr;
        $this->offerID = $offerID;
    }
    
    # calculate lp offer with data available
    function calc() {
        
        # use $this->*Details variables
        # if no details, use $DB to find values
        # reqDetails is null if lpOffer() isn't init through lpStore()
    
        if ($this->offerDetails === null) {
            $this->offerDetails = $this->DB->qa('SELECT * FROM `lpOffers` WHERE `offerID` = ?', array($this->offerID))[0]; }

        if ($price = $this->emdr->get($this->offerDetails['typeID'])) {
            $this->cache  = true;
            $price        = json_decode($price, true);
            $timeDiff     = (time() - $price['orders']['generatedAt'])/60/60; // time difference in hours
        }
        
    }
    
    # Get pricing info for required items
    # returns nothing, simply modifies existign variables
    function reqItems() {
        // todo: should we parse pricing data for required items with no cache of their own?
        if ($this->reqDetails === false) {
            return; }
        else if ($this->reqDetails === null) {
            # haven't yet gathered info
        }
        
        foreach ($this->reqDetails AS $reqItem) {
            if ($rprice = $emdr->get($reqItem['typeID'])) {
                $rprice = json_decode($rprice, true);
                $totalCost = $totalCost + ($rprice['orders']['sell'][0] * $reqItem['quantity']);
                //array_push($req, $reqItem['quantity']." x ".$reqItem['typeName']); 
            }
        }
    }
    
    
    # holder for old code.
    function FIX(){
            $totalCost = $offer['iskCost'];
            $req       = array(); // array that holds name of reuired items
            $cached    = false;   // flag
            $bpc       = false;   // flag

            // get pricing info on item

            
            // set required items
            if (isset($reqContainer[$offer['offerID']])){
                $reqItems = $reqContainer[$offer['offerID']];}
            else {
                $reqItems = array(); }

            $manReqItems = array();

            
            // Blueprints are special fucking buterflies
            if (strstr($offer['typeName'], " Blueprint")) {
                $bpc       = true;
                $name      = "1 x ".$offer['typeName']." Copy (".$offer['quantity']." run".($offer['quantity'] > 1 ? "s" : null).")"; 
                $label     = 'BP';
                $fresh     = array('info', 'Calculating with manufactured item');
                $manTypeID = $DB->q1(' 
                    SELECT      ProductTypeID  
                    FROM        invBlueprintTypes
                    WHERE       blueprintTypeID = ?', array($offer['typeID']));
                
                // set pricing info as the manufactured item
                if ($price = $emdr->get($manTypeID)) {
                    $price  = json_decode($price, true); 
                    $timeDiff = (time() - $price['orders']['generatedAt'])/60/60; // time difference in hours
                    $cached = true;
                }
                
                // Here we merge bill of materials for blueprints (remembering to multiple qnt with # of BPC runs)
                $manReqItems = array_merge(
                    // Get minerals needed
                    $DB->qa('
                        SELECT t.typeID,
                               t.typeName,
                               ROUND(greatest(0,sum(t.quantity)) * (1 + (b.wasteFactor / 100))) * ? AS quantity
                        FROM
                          (SELECT invTypes.typeid typeID,
                                  invTypes.typeName typeName,
                                  quantity
                           FROM invTypes,
                                invTypeMaterials,
                                invBlueprintTypes
                           WHERE invTypeMaterials.materialTypeID=invTypes.typeID
                            AND invBlueprintTypes.productTypeID = invTypeMaterials.typeID

                             AND invTypeMaterials.TypeID=?
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
                             AND bt.productTypeID=?
                             AND r.recycle=1) t
                        INNER JOIN invBlueprintTypes b ON (b.productTypeID = ?)

                        GROUP BY t.typeid,
                                 t.typeName', array($offer['quantity'], $manTypeID, $manTypeID, $manTypeID)),
                    // Get extra items needed
                    $DB->qa('
                        SELECT t.typeID AS    typeID,
                            t.typeName AS     typeName,
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
                        AND t.groupID = g.groupID', array($offer['quantity'], $manTypeID))); // append material needs to req items      
                
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
}

?>