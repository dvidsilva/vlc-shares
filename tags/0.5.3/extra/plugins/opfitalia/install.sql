
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.auth.username', '', '', 'plugins', 0, 'p_opfitalia_conf_authusername_label', 'p_opfitalia_conf_authusername_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.auth.password', '', '', 'plugins', 0, 'p_opfitalia_conf_authpassword_label', 'p_opfitalia_conf_authpassword_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.index.category', '0', '0', 'plugins', 0, 'p_opfitalia_conf_indexcategory_label', 'p_opfitalia_conf_indexcategory_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.base.url', 'http://www.opfitalia.net/mediacenter/index.php?page=ajax_show_folder&id=', 'http://www.opfitalia.net/mediacenter/index.php?page=ajax_show_folder&id=', 'plugins', 0, 'p_opfitalia_conf_baseurl_label', 'p_opfitalia_conf_baseurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.login.url', 'http://www.opfitalia.net/mediacenter/index.php?page=login', 'http://www.opfitalia.net/mediacenter/index.php?page=login', 'plugins', 0, 'p_opfitalia_conf_loginurl_label', 'p_opfitalia_conf_loginurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.request.timeout', '10', '10', 'plugins', 0, 'p_opfitalia_conf_requesttimeout_label', 'p_opfitalia_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.request.maxredirects', '10', '10', 'plugins', 0, 'p_opfitalia_conf_requestredirects_label', 'p_opfitalia_conf_requestredirects_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.hide.useragent', '0', '0', 'plugins', 3, 'p_opfitalia_conf_hideuseragent_label', 'p_opfitalia_conf_hideuseragent_desc',	'');
	
UPDATE plugins SET enabled=1 WHERE key = "opfitalia";

