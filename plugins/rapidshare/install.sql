INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rapidshare.premium.enabled','0','0','plugins',3,'p_rapidshare_conf_premiumenabled_label','p_rapidshare_conf_premiumenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rapidshare.premium.username','','','plugins',0,'p_rapidshare_conf_premiumusername_label','p_rapidshare_conf_premiumusername_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rapidshare.premium.password','','','plugins',0,'p_rapidshare_conf_premiumpassword_label','p_rapidshare_conf_premiumpassword_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rapidshare.request.timeout', '25', '25', 'plugins', 0, 'p_rapidshare_conf_requesttimeout_label', 'p_rapidshare_conf_requesttimeout_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "rapidshare";

