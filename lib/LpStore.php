<?php

class LpStore {
    
    public $offers;
    public $corpID;
    
    function __construct($corpID) {
        $this->corpID = $corpID;
        
        # set required item details for each offer
        # @todo: set to false if no items.
        $reqCont = array();

        foreach (Db::q(Sql::cReqItems, array($corpID)) AS $item) {  
            $reqCont[$item['offerID']][] = $item; 
        }

        foreach (Db::q(Sql::cOffers, array($corpID)) AS $offer) {
            $this->offers[$offer['offerID']] = new LpOffer($offer['offerID']);
            $this->offers[$offer['offerID']]->offerDetails = $offer;
            if (isset($reqCont[$offer['offerID']])) {
                $this->offers[$offer['offerID']]->reqDetails = $reqCont[$offer['offerID']]; }
            else {
                $this->offers[$offer['offerID']]->reqDetails = array(); }
            
            //$this->offers[$offer['offerID']]->calc('sell');
        }
    }
}

?>