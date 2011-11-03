INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'realdebrid.auth.username', '', '', 'plugins', 0, 'p_realdebrid_conf_authusername_label', 'p_realdebrid_conf_authusername_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'realdebrid.auth.password', '', '', 'plugins', 0, 'p_realdebrid_conf_authpassword_label', 'p_realdebrid_conf_authpassword_desc', '');

UPDATE plugins SET enabled=1 WHERE key = "realdebrid";