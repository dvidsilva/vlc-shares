
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'veetle.channels.cache.validity','3','3','plugins',0,'p_veetle_conf_channelscachevalidity_label','p_veetle_conf_channelscachevalidity_desc','');
	

UPDATE plugins SET enabled=1 WHERE key = "veetle";