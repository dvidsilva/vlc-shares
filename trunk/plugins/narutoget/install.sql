
/*
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.index.naruto.url', 'http://www.narutoget.com/page/10-naruto-episodes-subbed/', 'http://www.narutoget.com/page/10-naruto-episodes-subbed/', 'plugins', 0, 'p_narutoget_conf_indexnarutourl_label', 'p_narutoget_conf_indexnarutourl_desc', '');
*/	
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.index.shippuden.url', 'http://www.narutoget.com/naruto-shippuden-episodes/', 'http://www.narutoget.com/naruto-shippuden-episodes/', 'plugins', 0, 'p_narutoget_conf_indexshippudenurl_label', 'p_narutoget_conf_indexshippudenurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.request.timeout', '10', '10', 'plugins', 0, 'p_animeland_conf_requesttimeout_label', 'p_animeland_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.request.maxredirects', '0', '0', 'plugins', 0, 'p_animeland_conf_requestredirects_label', 'p_animeland_conf_requestredirects_desc', '');
	
UPDATE plugins SET enabled=1 WHERE key = "narutoget";

