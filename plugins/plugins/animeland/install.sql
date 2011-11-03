
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.base.url', 'http://www.animeland.it/', 'http://www.animeland.it/', 'plugins', 0, 'p_animeland_conf_baseurl_label', 'p_animeland_conf_baseurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.index.page', 'menu_streaming.html', 'menu_streaming.html', 'plugins', 0, 'p_animeland_conf_indexpage_label', 'p_animeland_conf_indexpage_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.request.timeout', '10', '10', 'plugins', 0, 'p_animeland_conf_requesttimeout_label', 'p_animeland_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.request.maxredirects', '0', '0', 'plugins', 0, 'p_animeland_conf_requestredirects_label', 'p_animeland_conf_requestredirects_desc', '');

	
UPDATE plugins SET enabled=1 WHERE key = "animeland";

