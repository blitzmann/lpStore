<?php

class LpStore {
    
    public $offers;
    public $corpID;
    
    function __construct($corpID) {
        $this->corpID = $corpID;

        $req = (new Query_CorpReqItems($corpID))->execute();
        
        foreach ((new Query_CorpOffers($corpID))->execute() AS $o) {
            $this->offers[$o['offerID']] = (new LpOffer($o['offerID'], $o, (!isset($req[$o['offerID']]) ? array() : $req[$o['offerID']])))->calc(MARKET_MODE);
        }
    }
    
    public function getStations() {
        return (new Query_CorpStations($this->corpID))->execute();
    }
}

?>