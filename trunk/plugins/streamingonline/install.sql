
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'streamingonline.hide.useragent', '0', '0', 'plugins', 3, 'p_streamingonline_conf_hideuseragent_label', 'p_streamingonline_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'streamingonline.request.timeout', '10', '10', 'plugins', 0, 'p_streamingonline_conf_requesttimeout_label', 'p_streamingonline_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'streamingonline.request.maxredirects', '0', '0', 'plugins', 0, 'p_streamingonline_conf_requestredirects_label', 'p_streamingonline_conf_requestredirects_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "streamingonline";

