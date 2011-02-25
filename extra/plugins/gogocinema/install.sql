
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.url', 'http://www.gogocinema.com/index.php', 'http://www.gogocinema.com/index.php', 'plugins', 0, 'p_gogocinema_conf_indexurl_label', 'p_gogocinema_conf_indexurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.alpha.url', 'http://www.gogocinema.com/disp_abcd.php?letter=', 'http://www.gogocinema.com/disp_abcd.php?letter=', 'plugins', 0, 'p_gogocinema_conf_indexalphaurl_label', 'p_gogocinema_conf_indexalphaurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.type.url', 'http://www.gogocinema.com/disp_genre.php?genre=', 'http://www.gogocinema.com/disp_genre.php?genre=', 'plugins', 0, 'p_gogocinema_conf_indextypeurl_label', 'p_gogocinema_conf_indextypeurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.year.url', 'http://www.gogocinema.com/disp_year.php?year=', 'http://www.gogocinema.com/disp_year.php?year=', 'plugins', 0, 'p_gogocinema_conf_indexyearurl_label', 'p_gogocinema_conf_indexyearurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.search.url', 'http://www.gogocinema.com/disp_search.php?search=', 'http://www.gogocinema.com/disp_search.php?search=', 'plugins', 0, 'p_gogocinema_conf_indexsearchurl_label', 'p_gogocinema_conf_indexsearchurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.index.video.url', 'http://www.gogocinema.com/movie.php?movie_id=', 'http://www.gogocinema.com/movie.php?movie_id=', 'plugins', 0, 'p_gogocinema_conf_indexvideourl_label', 'p_gogocinema_conf_indexvideourl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.request.timeout', '25', '25', 'plugins', 0, 'p_gogocinema_conf_requesttimeout_label', 'p_gogocinema_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.request.maxredirects', '10', '10', 'plugins', 0, 'p_gogocinema_conf_requestredirects_label', 'p_gogocinema_conf_requestredirects_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'gogocinema.hide.useragent', '0', '0', 'plugins', 3, 'p_gogocinema_conf_hideuseragent_label', 'p_gogocinema_conf_hideuseragent_desc',	'');
	
UPDATE plugins SET enabled=1 WHERE key = "gogocinema";

