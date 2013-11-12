<?php

abstract class Sql {
    
    # Given offerID, return offer info along with item name
    const oDetails = 'SELECT a.*, b.`typeName` FROM `lpOffers` a NATURAl JOIN `invTypes` b WHERE `offerID` = ? LIMIT 0,1';

    # Given offerID, return required items for offer, along with their names
    const rDetails = 'SELECT a.*, b.typeName FROM lpOfferRequirements a NATURAL JOIN invTypes b WHERE `offerID` = ?';

    # Given typeID and assuming it's a BPC, return typeID of related BPC
    const manTypeID = 'SELECT `ProductTypeID` FROM `invBlueprintTypes` WHERE `blueprintTypeID` = ?';

    # Given BPC typeID and number of runs, find mineral requirements for BPCs
    # @todo: find a more efficient method if possible
    const manMinerals = <<<'SQL'
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
SQL;
    
    # Given BPC typeID and number of runs, find extra requirements for BPCs
    const manExtra = <<<'SQL'
SELECT t.typeID AS typeID, t.typeName AS typeName, (r.quantity * ?) AS quantity
FROM ramTypeRequirements r, invTypes t, invBlueprintTypes bt, invGroups g
WHERE r.requiredTypeID = t.typeID AND
    r.typeID = bt.blueprintTypeID AND
    r.activityID = 1 AND
    bt.productTypeID = ? AND 
    g.categoryID != 16 AND 
    t.groupID = g.groupID
SQL;
    
    # Given corpID, gather all required items for store offers
    const cReqItems = <<<'SQL'
SELECT      a.typeID, a.quantity, b.typeName, a.offerID
FROM        lpOfferRequirements a
INNER JOIN  invTypes b ON (b.typeID = a.typeID)
INNER JOIN  lpStore c ON (a.offerID = c.offerID)
WHERE       c.corporationID = ?
SQL;
    
    # Given corpID, gather all corp offers
    const cOffers = <<<'SQL'
SELECT `lpStore`.* , `invTypes`.`typeName`, `lpOffers`.*
FROM lpStore
NATURAL JOIN lpOffers
NATURAL JOIN invTypes
WHERE `lpStore`.`corporationID` = ?
ORDER BY 
    `lpOffers`.`lpCost`, 
    `lpOffers`.iskCost, 
    `invTypes`.`typeName`
SQL;
   
}
?>