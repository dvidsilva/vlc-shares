
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'filmstream.hide.useragent', '0', '0', 'plugins', 3, 'p_filmstream_conf_hideuseragent_label', 'p_filmstream_conf_hideuseragent_desc',	'');


UPDATE plugins SET enabled=1 WHERE key = "filmstream";

