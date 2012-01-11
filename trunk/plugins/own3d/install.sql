
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'own3d.hide.useragent', '0', '0', 'plugins', 3, 'p_own3d_conf_hideuseragent_label', 'p_own3d_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'own3d.request.timeout', '10', '10', 'plugins', 0, 'p_own3d_conf_requesttimeout_label', 'p_own3d_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'own3d.source', 'http://www.own3d.tv/live/?all', 'http://www.own3d.tv/live/?all', 'plugins', 0, 'p_own3d_conf_source_label', 'p_own3d_conf_source_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'own3d.default.quality', '2', '2', 'plugins', 0, 'p_own3d_conf_defaultquality_label', 'p_own3d_conf_defaultquality_desc',	'');


UPDATE plugins SET enabled=1 WHERE key = "own3d";

