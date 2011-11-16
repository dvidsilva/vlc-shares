
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'youporn.hide.useragent', '0', '0', 'plugins', 3, 'p_youporn_conf_hideuseragent_label', 'p_youporn_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'youporn.request.timeout', '10', '10', 'plugins', 0, 'p_youporn_conf_requesttimeout_label', 'p_youporn_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'youporn.request.maxredirects', '10', '10', 'plugins', 0, 'p_youporn_conf_requestredirects_label', 'p_youporn_conf_requestredirects_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "youporn";

