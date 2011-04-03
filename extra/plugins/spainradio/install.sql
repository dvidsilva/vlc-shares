INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'spainradio.direct.enabled', 1, 1, 'plugins', 3, 'p_spainradio_conf_directenabled_label', 'p_spainradio_conf_directenabled_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "spainradio";

