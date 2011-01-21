
CREATE TABLE plg_megavideo (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    idVideo VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'default',
    label VARCHAR(255) NOT NULL,
    description TEXT NULL
);
 
CREATE INDEX "plg_megavideo_id" ON "plg_megavideo" ("id");

--INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
--	'megavideo',
--	'X_VlcShares_Plugins_Megavideo', 
--	'X/VlcShares/Plugins/Megavideo.php',
--	'p_megavideo_plglabel',
--	'p_megavideo_plgdesc',
--	1,
--	1
--);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'megavideo.premium.enabled','0','0','plugins',3,'p_megavideo_conf_premiumenabled_label','p_megavideo_conf_premiumenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'megavideo.premium.username','','','plugins',0,'p_megavideo_conf_premiumusername_label','p_megavideo_conf_premiumusername_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'megavideo.premium.password','','','plugins',0,'p_megavideo_conf_premiumpassword_label','p_megavideo_conf_premiumpassword_desc','');


INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('NBDB38CE',
    'BBT',
    'PILOT',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('ZUWSN1B9',
    'BBT',
    'puntata 1',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('T65OW4BR',
    'American Dad',
    'puntata X',
    '');

UPDATE plugins SET enabled=1 WHERE key = "megavideo";