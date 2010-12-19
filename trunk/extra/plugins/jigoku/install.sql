
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jigoku.base.url', 'http://www.jigoku.it/anime-streaming/', 'http://www.jigoku.it/anime-streaming/', 'plugins', 0, 'p_jigoku_conf_baseurl_label', 'p_jigoku_conf_baseurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jigoku.request.timeout', '10', '10', 'plugins', 0, 'p_jigoku_conf_requesttimeout_label', 'p_jigoku_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jigoku.request.maxredirects', '10', '10', 'plugins', 0, 'p_jigoku_conf_requestredirects_label', 'p_jigoku_conf_requestredirects_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jigoku.hide.useragent', '0', '0', 'plugins', 3, 'p_jigoku_conf_hideuseragent_label', 'p_jigoku_conf_hideuseragent_desc',	'');
	
UPDATE plugins SET enabled=1 WHERE key = "jigoku";

