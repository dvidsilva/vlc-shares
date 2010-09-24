

INSERT INTO configs ( `key`, `value`, `default`, `section`, `type` ) VALUES (
	'languageFile', 
	'it_IT.ini',
	'en_GB.ini',
	'general',
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type ) VALUES (
	'debug.enabled', 
	'1',
	'0',
	'general',
	3
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, class ) VALUES (
	'debug.level', 
	'3',
	'1',
	'general',
	3,
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, class ) VALUES (
	'debug.path', 
	'',
	'',
	'general',
	0,
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'apache.port', 
	'80',
	'80',
	'general',
	0,
	'config_general_apache_port_label',
	'config_general_apache_port_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description ) VALUES (
	'path', 
	'/usr/bin/vlc',
	'/usr/bin/vlc',
	'vlc',
	0,
	'config_vlc_path_label',
	'config_vlc_path_desc'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'args', 
	'--play-and-exit {%source%} --sout="#{%profile%}:{%output%}" --sout-keep {%subtitles%} {%audio%} {%filters%}',
	'--play-and-exit {%source%} --sout="#{%profile%}:{%output%}" --sout-keep {%subtitles%} {%audio%} {%filters%}',
	'vlc',
	0,
	'config_vlc_args_label',
	'config_vlc_args_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'commander.http.command', 
	'http://{%host%}:{%port%}/requests/status.xml{%command%}',
	'http://{%host%}:{%port%}/requests/status.xml{%command%}',
	'vlc',
	0,
	'config_vlc_commander_http_command_label',
	'config_vlc_commander_http_command_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'commander.http.host', 
	'127.0.0.1',
	'127.0.0.1',
	'vlc',
	0,
	'config_vlc_commander_http_host_label',
	'config_vlc_commander_http_host_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'commander.http.port', 
	'4212',
	'4212',
	'vlc',
	0,
	'config_vlc_commander_http_port_label',
	'config_vlc_commander_http_port_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'commander.http.timeout', 
	'1',
	'1',
	'vlc',
	0,
	'config_vlc_commander_http_timeout_label',
	'config_vlc_commander_http_timeout_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mediainfo.enabled', 
	'1',
	'1',
	'helpers',
	3,
	'config_helpers_mediainfo_enabled_label',
	'config_helpers_mediainfo_enabled_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mediainfo.path', 
	'/usr/bin/mediainfo',
	'/usr/bin/mediainfo',
	'helpers',
	0,
	'config_helpers_mediainfo_path_label',
	'config_helpers_mediainfo_path_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'ffmpeg.enabled', 
	'1',
	'1',
	'helpers',
	3,
	'config_helpers_ffmpeg_enabled_label',
	'config_helpers_ffmpeg_enabled_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'ffmpeg.path', 
	'/usr/bin/ffmpeg',
	'/usr/bin/ffmpeg',
	'helpers',
	0,
	'config_helpers_ffmpeg_path_label',
	'config_helpers_ffmpeg_path_desc',
	NULL
);

