INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'veetle.server.ip','77.67.108.152','77.67.108.152','plugins',1,'p_veetle_conf_serverip_label','p_veetle_conf_serverip_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'veetle.channels.cache.validity','3','3','plugins',0,'p_veetle_conf_channelscachevalidity_label','p_veetle_conf_channelscachevalidity_desc','');
	

UPDATE plugins SET enabled=1 WHERE key = "veetle";