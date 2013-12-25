<?php

/*
    Class can either be initialized as a stand alone object, 
    in which case the class will pull the data from the DB and calculate offer 
    details, or it can be initialized via lpStore in which case lpStore will
    supply details for the offer and lpOffer simply calculates prices.
    
    The reason for this is to cut down on the number of queries. 
    If we initialized lpOffer() by itself for every LP Store 
    offer, that's potentially hundreds of queries just to get 
    the info for the entire LP Store. lpStore() will do one big 
    query to get all offers and 1 query to get required items, 
    and pass this info along to lpOffer()
    
    @todo: allow switching req and man costs between sell and buy?
*/

class LpOffer {

    private $bpcCache;
    
    ## Details from database
    public $offerDetails;
    public $reqDetails;
    
    ## Properties of the offer itself
    public $offerID;
    public $cached   = false; # was pricing info cached for offer?
    public $bpc      = false; # is offer a BPC?
    public $price    = null;  # pricing info for item (if blueprint, pricing info of manufactured item)
    public $timeDiff = null;  # between now and when price was cached
    public $totalCost = 0;    # total cost of offer (init isk/req/man)
    public $margin    = 0;    # profit of offer after subtraction total cost
    public $profit    = 0;    # pure profit of selling the offer
    public $lp2isk    = 0;    # isk/lp after all calculations
    public $totVolume = 0; 
    ## Details for manufacturing
    public $manDetails = array();
    public $manTypeID = null;
    
    public $noCache  = array(); # store typeID => name of items with no price cache
    
    public function __get($field) {
        return $this->offerDetails[$field]; }
    
    /*
        Class is initiated with basic information. lpStore is able to pass along 
        details of the offer, in which case {offer|req}Details are set to 
        something other than null. 
        
        lpOffer is meant to collect info and store in class properties for access.
    */
    public function __construct($offerID, $offerDetails = null, $reqDetails = null) {
        $this->offerID      = $offerID;
        $this->offerDetails = $offerDetails;
        $this->reqDetails   = $reqDetails;
        # @todo: list orderDetails to variables ?
    }
    
    /*
        calc() takes care of initializing calculation functions
        It is also responsible for gathering data for items if needed.

        Returns self
    */
    
    public function calc() {
        try {            
            if ($this->offerDetails === null) {
                $this->offerDetails = Db::q(Sql::oDetails, array($this->offerID))[0]; }
            
            if (empty($this->offerDetails)) { throw Exception('No offer details available.'); }
            
            if ($this->reqDetails === null) {
                $this->reqDetails = Db::q(Sql::rDetails, array($this->offerID)); }
            
            if (strstr($this->offerDetails['typeName'], " Blueprint")) {
                # If this is a bpc, set the flag and run bpc function
                $this->bpc = true; 
                $this->bpcCalc();
            } else {
                # if this is not a blueprint, go ahead and set price via given known typeID
                try {
                    $price = new Price(Emdr::get($this->offerDetails['typeID']));
                    $this->cached      = true;
                    $this->price       = $price->{Prefs::get('marketOffer')}[0];
                    $this->totVolume   = $price->{Prefs::get('marketOffer')}[1];
                    $this->timeDiff    = (time() - $price->generatedAt)/60/60; # time difference in hours
                } catch (Exception $e) {
                    array_push($this->noCache, $this->offerDetails['typeName']); }
            }
            
            foreach ($this->reqDetails AS &$reqItem) {
                try {
                    $price = new Price(Emdr::get($reqItem['typeID']));
                    $reqItem['price']    = $price->{Prefs::get('marketReq')}[0];
                    $reqItem['totPrice'] = $reqItem['price']* $reqItem['quantity']; 
                } catch (Exception $e) {
                    array_push($this->noCache, $reqItem['typeName']); }
            }
            
            # calculate total cost
            $this->totalCost = $this->offerDetails['iskCost'];
            foreach ($this->reqDetails AS &$reqItem) {
                $this->totalCost += ($reqItem['quantity'] * $reqItem['price']); }
                
            foreach ($this->manDetails AS &$manItem) {
                $this->totalCost += ($manItem['totQty'] * $manItem['price']); }
            
            # calculate profits / isk/lp
            $this->profit = $this->price * $this->offerDetails['quantity'];
            $this->margin = $this->profit - $this->totalCost;
            $this->lp2isk = $this->margin / $this->offerDetails['lpCost'];
        } catch (Exception $e) {
            die($e);
        }
        
        return $this;
    }

    /*
        bpc() finds the pricing info for the manufactured item, which lpOffer()
        will use to calculate isk/lp. It also sets required building materials
    */
    private function bpcCalc() {
        if (!$this->bpc) { return; } # Something's gone wrong, don't do this if not a BPC
        
        # Do this in template
        // $name =  "1 x ".$offer['typeName']." Copy (".$offer['quantity']." run".($offer['quantity'] > 1 ? "s" : null).")"; 

        $this->manTypeID = Db::qColumn('
            SELECT `ProductTypeID` 
            FROM `invBlueprintTypes` 
            WHERE `blueprintTypeID` = :typeID', 
            array(':typeID' => $this->offerDetails['typeID']));
        
        # set pricing info per the manufactured item
        try {
            $price = new Price(Emdr::get($this->manTypeID));
            $this->cached      = true;
            $this->price       = $price->{Prefs::get('marketOffer')}[0];
            $this->totVolume   = $price->{Prefs::get('marketOffer')}[1];
            $this->timeDiff    = (time() - $price->generatedAt)/60/60; # time difference in hours
        } catch (Exception $e) {
            array_push($this->noCache, $this->offerDetails['typeName']); }
        
        # find cached result of BPC manufacturing materials
        $this->manDetails = (new Query_OfferMaterials($this->manTypeID, $this->offerDetails['quantity']))->execute();
    }
    
    public function getSimilar() {
        return (new Query_OfferStores($this->offerDetails['typeID']))->execute();
    }
    
    public function getStores() {
        return (new Query_OfferStores($this->offerID))->execute();
    }
    
    public function getReqCount() {
        return count($this->reqDetails);
    }
    
    public function getManCount() {
        return count($this->manDetails);
    }
    
    /*
        This gets the name of the item being used as the basis for pricing data. 
        Basically, if blueprint, gets the name of the manufactured object
        
        @todo: possibly find a better way to handle blueprints in general.
    */
    public function getProductName() {
        if ($this->bpc) {
            return str_replace('Blueprint', '', $this->typeName); }
        
        return $this->typeName;
    }
    
    /*
        This is the display name for the offer. If BPC, do a little bit of work
        (add # runs)
        
        @todo: Possibly replace offer qty with 1 for BPCs and add a different 
        property to the class for # runs. This way, we can get rid of special 
        cases like this
        
    */
    public function getDisplayName() {
        if ($this->bpc) {
            return '1 x '.$this->typeName.' Copy ('.$this->quantity.' run'.($this->quantity > 1 ? 's' : null).')'; }
        
        return $this->quantity.'x '.$this->typeName;
    }
}

?>