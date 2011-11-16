
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpgui.hide.useragent', '0', '0', 'plugins', 3, 'p_rtmpgui_conf_hideuseragent_label', 'p_rtmpgui_conf_hideuseragent_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpgui.request.timeout', '10', '10', 'plugins', 0, 'p_rtmpgui_conf_requesttimeout_label', 'p_rtmpgui_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpgui.source', 'http://apps.ohlulz.com/rtmpgui/list.xml', 'http://apps.ohlulz.com/rtmpgui/list.xml', 'plugins', 0, 'p_rtmpgui_conf_source_label', 'p_rtmpgui_conf_source_desc', '');


UPDATE plugins SET enabled=1 WHERE key = "rtmpgui";

