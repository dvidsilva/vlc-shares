INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'direttarai.direct.enabled', 1, 1, 'plugins', 3, 'p_direttarai_conf_directenabled_label', 'p_direttarai_conf_directenabled_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "direttarai";

