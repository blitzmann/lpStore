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
  
   
}
?>