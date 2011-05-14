
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.remoteapi.ip', 'localhost', 'localhost', 'plugins', 0, 'p_jdownloader_conf_remoteapiip_label', 'p_jdownloader_conf_remoteapiip_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.remoteapi.port', '10025', '10025', 'plugins', 0, 'p_jdownloader_conf_remoteapiport_label', 'p_jdownloader_conf_remoteapiport_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.request.timeout', '1', '1', 'plugins', 0, 'p_jdownloader_conf_requesttimeout_label', 'p_jdownloader_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.download.enabled', '1', '1', 'plugins', 3, 'p_jdownloader_conf_downloadenabled_label', 'p_jdownloader_conf_downloadenabled_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.version.isnightly', '0', '0', 'plugins', 3, 'p_jdownloader_conf_versionisnightly_label', 'p_jdownloader_conf_versionisnightly_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'jdownloader.statistics.enabled', '1', '1', 'plugins', 3, 'p_jdownloader_conf_statisticsenabled_label', 'p_jdownloader_conf_statisticsenabled_desc',	'');
	
UPDATE plugins SET enabled=1 WHERE key = "jdownloader";

