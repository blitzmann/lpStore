<?php

abstract class Sql {
    
    # Given offerID, return offer info along with item name
    const oDetails = 'SELECT a.*, b.`typeName` FROM `lpOffers` a NATURAl JOIN `invTypes` b WHERE `offerID` = ? LIMIT 0,1';

    # Given offerID, return required items for offer, along with their names
    const rDetails = 'SELECT a.*, b.typeName FROM lpOfferRequirements a NATURAL JOIN invTypes b WHERE `offerID` = ? ORDER BY a.quantity ASC';
   
}
?>