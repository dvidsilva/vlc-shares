
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'tvlinks.hide.useragent', '0', '0', 'plugins', 3, 'p_tvlinks_conf_hideuseragent_label', 'p_tvlinks_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'tvlinks.request.timeout', '10', '10', 'plugins', 0, 'p_tvlinks_conf_requesttimeout_label', 'p_tvlinks_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'tvlinks.request.maxredirects', '0', '0', 'plugins', 0, 'p_tvlinks_conf_requestredirects_label', 'p_tvlinks_conf_requestredirects_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "tvlinks";

