
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'weebtv.auth.enabled','0','0','plugins',3,'p_weebtv_conf_authenabled_label','p_weebtv_conf_authenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'weebtv.auth.username','','','plugins',0,'p_weebtv_conf_authusername_label','p_weebtv_conf_authusername_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'weebtv.auth.password','','','plugins',0,'p_weebtv_conf_authpassword_label','p_weebtv_conf_authpassword_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'weebtv.request.timeout', '10', '10', 'plugins', 0, 'p_weebtv_conf_requesttimeout_label', 'p_weebtv_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'weebtv.request.maxredirects', '0', '0', 'plugins', 0, 'p_weebtv_conf_requestredirects_label', 'p_weebtv_conf_requestredirects_desc', '');



UPDATE plugins SET enabled=1 WHERE key = "weebtv";

