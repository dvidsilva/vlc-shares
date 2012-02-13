INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.selection.priority', 'cdn', 'cdn', 'plugins', 1, 'p_hulu_conf_selectionpriority_label', 'p_hulu_conf_selectionpriority_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.video.quality', '400_h264', '400_h264', 'plugins', 1, 'p_hulu_conf_videoquality_label', 'p_hulu_conf_videoquality_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.preferred.cdn', 'limelight', 'limelight', 'plugins', 1, 'p_hulu_conf_preferredcdn_label', 'p_hulu_conf_preferredcdn_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.hide.useragent', '1', '1', 'plugins', 3, 'p_hulu_conf_hideuseragent_label', 'p_hulu_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.request.timeout', '25', '25', 'plugins', 0, 'p_hulu_conf_requesttimeout_label', 'p_hulu_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hulu.request.maxredirects', '10', '10', 'plugins', 0, 'p_hulu_conf_requestredirects_label', 'p_hulu_conf_requestredirects_desc', '');
	

	
UPDATE plugins SET enabled=1 WHERE key = "hulu";
