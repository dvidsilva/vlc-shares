
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.file.enabled','1','1','plugins',3,'p_audioswitcher_conf_fileenabled_label','p_audioswitcher_conf_fileenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.file.extensions','mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3','mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3','plugins',0,'p_audioswitcher_conf_fileextensions_label','p_audioswitcher_conf_fileextensions_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.infile.enabled','1','1','plugins',3,'p_audioswitcher_conf_infileenabled_label','p_audioswitcher_conf_infileenabled_desc','');

UPDATE plugins SET enabled=1 WHERE key = "audioSwitcher";
