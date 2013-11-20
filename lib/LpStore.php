<?php

class LpStore {
    
    public $offers;
    public $corpID;
    
    function __construct($corpID) {
        $this->corpID = $corpID;

        $req = (new Query_CorpReqItems($corpID))->execute();
        
        foreach ((new Query_CorpOffers($corpID))->execute() AS $o) {
            $this->offers[$o['offerID']] = new LpOffer($o['offerID']);
            $this->offers[$o['offerID']]->offerDetails = $o;
            
            if (isset($req[$o['offerID']])) {
                $this->offers[$o['offerID']]->reqDetails = $req[$o['offerID']]; }
            else {
                $this->offers[$o['offerID']]->reqDetails = array(); }
            
            $this->offers[$o['offerID']]->calc('sell');
        }
    }
    
    public function getStations() {
        return (new Query_CorpStations($this->corpID))->execute();
    }
}

?>