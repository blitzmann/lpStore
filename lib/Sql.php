<?php

abstract class Sql {
    
    # Given offerID, return offer info along with item name
    const oDetails = 'SELECT a.*, b.`typeName` FROM `lpOffers` a NATURAl JOIN `invTypes` b WHERE `offerID` = ? LIMIT 0,1';

    # Given offerID, return required items for offer, along with their names
    const rDetails = 'SELECT a.*, b.typeName FROM lpOfferRequirements a NATURAL JOIN invTypes b WHERE `offerID` = ?';

    # Given typeID and assuming it's a BPC, return typeID of related BPC
    const manTypeID = 'SELECT `ProductTypeID` FROM `invBlueprintTypes` WHERE `blueprintTypeID` = ?';

    # Given BPC typeID, material requirements for BPCs (incorporating waste factor)
    # Would people be more interested in materials per run? 
    const manMaterials = <<<'SQL'
SELECT typeID, typeName, SUM(quantity) * :quantity AS quantity
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