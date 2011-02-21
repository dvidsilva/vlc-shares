INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'allsp.request.timeout', '10', '10', 'plugins', 0, 'p_allsp_conf_requesttimeout_label', 'p_allsp_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'allsp.request.maxredirects', '0', '0', 'plugins', 0, 'p_allsp_conf_requestredirects_label', 'p_allsp_conf_requestredirects_desc', '');
	
UPDATE plugins SET enabled=1 WHERE key = "allsp";

