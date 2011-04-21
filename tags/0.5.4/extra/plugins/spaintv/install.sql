INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'spaintv.direct.enabled', 1, 1, 'plugins', 3, 'p_spaintv_conf_directenabled_label', 'p_spaintv_conf_directenabled_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "spaintv";

