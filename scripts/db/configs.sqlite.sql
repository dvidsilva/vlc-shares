

INSERT INTO configs ( `key`, `value`, `default`, `section`, `type`, label, description ) VALUES (
	'languageFile', 
	'en_GB.ini',
	'en_GB.ini',
	'general',
	1,
	'config_general_languageFile_label',
	'config_general_languageFile_desc'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'extraPlugins', 
	'',
	'',
	'general',
	0,
	'config_general_extraPlugins_label',
	'config_general_extraPlugins_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description ) VALUES (
	'debug.enabled', 
	'0',
	'0',
	'general',
	3,
	'config_general_debug_enabled_label',
	'config_general_debug_enabled_desc'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'debug.level', 
	'1',
	'1',
	'general',
	5,
	'config_general_debug_level_label',
	'config_general_debug_level_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'debug.path', 
	'',
	'',
	'general',
	0,
	'config_general_debug_path_label',
	'config_general_debug_path_desc',
	'advanced fileBrowser fileBrowser-dir'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'threads.forker', 
	'http://localhost/vlc-shares/threads/start',
	'http://localhost/vlc-shares/threads/start',
	'general',
	0,
	'config_general_threads_forker_label',
	'config_general_threads_forker_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'threads.logger', 
	'0',
	'0',
	'general',
	3,
	'config_general_threads_logger_label',
	'config_general_threads_logger_desc',
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

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'path', 
	'/usr/bin/vlc',
	'/usr/bin/vlc',
	'vlc',
	0,
	'config_vlc_path_label',
	'config_vlc_path_desc',
	'fileBrowser'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description ) VALUES (
	'version', 
	'2.x',
	'2.x',
	'vlc',
	1,
	'config_vlc_version_label',
	'config_vlc_version_desc'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description ) VALUES (
	'adapter.log', 
	'0',
	'0',
	'vlc',
	3,
	'config_vlc_adapterlog_label',
	'config_vlc_adapterlog_desc'
);


INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'args', 
	'{%source%} --play-and-exit --sout="{%profile%}" --sout-keep --http-caching="10000" --sout-mux-caching="20000" {%subtitles%} {%audio%} {%filters%}',
	'{%source%} --play-and-exit --sout="{%profile%}" --sout-keep --http-caching="10000" --sout-mux-caching="20000" {%subtitles%} {%audio%} {%filters%}',
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
	'5',
	'5',
	'vlc',
	0,
	'config_vlc_commander_http_timeout_label',
	'config_vlc_commander_http_timeout_desc',
	'advanced'
);

--INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
--	'mediainfo.enabled', 
--	'1',
--	'1',
--	'helpers',
--	3,
--	'config_helpers_mediainfo_enabled_label',
--	'config_helpers_mediainfo_enabled_desc',
--	NULL
--);
--
--INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
--	'mediainfo.path', 
--	'/usr/bin/mediainfo',
--	'/usr/bin/mediainfo',
--	'helpers',
--	0,
--	'config_helpers_mediainfo_path_label',
--	'config_helpers_mediainfo_path_desc',
--	NULL
--);


INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'paginator.perpage', 
	'400',
	'400',
	'helpers',
	0,
	'config_helpers_paginator_perpage_label',
	'config_helpers_paginator_perpage_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'devices.gui', 
	'X_VlcShares_Plugins_WebkitRenderer',
	'X_VlcShares_Plugins_WebkitRenderer',
	'helpers',
	1,
	'config_helpers_devices_gui_label',
	'config_helpers_devices_gui_desc',
	''
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'devices.profile', 
	'1',
	'1',
	'helpers',
	1,
	'config_helpers_devices_profile_label',
	'config_helpers_devices_profile_desc',
	''
);

--INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
--	'streamer.enabled', 
--	'1',
--	'1',
--	'helpers',
--	3,
--	'config_helpers_streamer_enabled_label',
--	'config_helpers_streamer_enabled_desc',
--	'advanced'
--);

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
	'fileBrowser'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpdump.enabled', 
	'1',
	'1',
	'helpers',
	3,
	'config_helpers_rtmpdump_enabled_label',
	'config_helpers_rtmpdump_enabled_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpdump.path', 
	'/usr/sbin/rtmpgw',
	'/usr/sbin/rtmpgw',
	'helpers',
	0,
	'config_helpers_rtmpdump_path_label',
	'config_helpers_rtmpdump_path_desc',
	'fileBrowser'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'rtmpdump.port', 
	'8081',
	'8081',
	'helpers',
	0,
	'config_helpers_rtmpdump_port_label',
	'config_helpers_rtmpdump_port_desc',
	'advanced'
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'sopcast.enabled', 
	'1',
	'1',
	'helpers',
	3,
	'config_helpers_sopcast_enabled_label',
	'config_helpers_sopcast_enabled_desc',
	NULL
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'sopcast.path', 
	'/usr/bin/sp-sc',
	'/usr/bin/sp-sc',
	'helpers',
	0,
	'config_helpers_sopcast_path_label',
	'config_helpers_sopcast_path_desc',
	'fileBrowser'
);
