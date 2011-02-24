
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.index.url', 'http://www.dbforever.net/home.php', 'http://www.dbforever.net/home.php', 'plugins', 0, 'p_dbforever_conf_indexurl_label', 'p_dbforever_conf_indexurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.request.timeout', '10', '10', 'plugins', 0, 'p_dbforever_conf_requesttimeout_label', 'p_dbforever_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.request.maxredirects', '0', '0', 'plugins', 0, 'p_dbforever_conf_requestredirects_label', 'p_dbforever_conf_requestredirects_desc', '');

	
UPDATE plugins SET enabled=1 WHERE key = "dbforever";

