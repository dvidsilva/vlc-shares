
-- UPDATE DATABASE FROM 0.5.1_beta4 -> 0.5.1


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'emptylists',
	'X_VlcShares_Plugins_EmptyLists', 
	'X/VlcShares/Plugins/EmptyLists.php',
	'p_emptylists_plglabel',
	'p_emptylists_plgdesc',
	1,
	1
);

