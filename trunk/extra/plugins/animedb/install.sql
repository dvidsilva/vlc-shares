DELETE FROM configs WHERE `key` LIKE 'animedb.%';

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.auth.username', '', '', 'plugins', 0, 'p_animedb_conf_authusername_label', 'p_animedb_conf_authusername_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.auth.password', '', '', 'plugins', 0, 'p_animedb_conf_authpassword_label', 'p_animedb_conf_authpassword_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.index.url', 'http://animedb.tv/forum/liste.php', 'http://animedb.tv/forum/liste.php', 'plugins', 0, 'p_animedb_conf_indexurl_label', 'p_animedb_conf_indexurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.base.url', 'http://animedb.tv/forum/', 'http://animedb.tv/forum/', 'plugins', 0, 'p_animedb_conf_baseurl_label', 'p_animedb_conf_baseurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.login.url', 'http://animedb.tv/forum/login.php?do=login', 'http://animedb.tv/forum/login.php?do=login', 'plugins', 0, 'p_animedb_conf_loginurl_label', 'p_animedb_conf_loginurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.request.timeout', '25', '25', 'plugins', 0, 'p_animedb_conf_requesttimeout_label', 'p_animedb_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.request.maxredirects', '10', '10', 'plugins', 0, 'p_animedb_conf_requestredirects_label', 'p_animedb_conf_requestredirects_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.hide.useragent', '0', '0', 'plugins', 3, 'p_animedb_conf_hideuseragent_label', 'p_animedb_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animedb.scraper.alternative.enabled', '0', '0', 'plugins', 3, 'p_animedb_conf_scraperalternativeenabled_label', 'p_animedb_conf_scraperalternativeenabled_desc',	'');

	
UPDATE plugins SET enabled=1 WHERE key = "animedb";

