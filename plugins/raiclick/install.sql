
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'raiclick.hide.useragent', '0', '0', 'plugins', 3, 'p_raiclick_conf_hideuseragent_label', 'p_raiclick_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'raiclick.request.timeout', '10', '10', 'plugins', 0, 'p_raiclick_conf_requesttimeout_label', 'p_raiclick_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'raiclick.request.maxredirects', '0', '0', 'plugins', 0, 'p_raiclick_conf_requestredirects_label', 'p_raiclick_conf_requestredirects_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "raiclick";

