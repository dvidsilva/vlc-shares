
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'2shared.hide.useragent', '0', '0', 'plugins', 3, 'p_2shared_conf_hideuseragent_label', 'p_2shared_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'2shared.request.timeout', '10', '10', 'plugins', 0, 'p_2shared_conf_requesttimeout_label', 'p_2shared_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'2shared.request.maxredirects', '10', '10', 'plugins', 0, 'p_2shared_conf_requestredirects_label', 'p_2shared_conf_requestredirects_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "2shared";

