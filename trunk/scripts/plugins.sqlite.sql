

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'fileSystem',
	'X_VlcShares_Plugins_FileSystem', 
	'X/VlcShares/Plugins/FileSystem.php',
	'p_filesystem_plglabel',
	'p_filesystem_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'megavideo',
	'X_VlcShares_Plugins_Megavideo', 
	'X/VlcShares/Plugins/Megavideo.php',
	'p_megavideo_plglabel',
	'p_megavideo_plgdesc',
	1,
	1
);



INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'profiles',
	'X_VlcShares_Plugins_Profiles', 
	'X/VlcShares/Plugins/Profiles.php',
	'p_profiles_plglabel',
	'p_profiles_plgdesc',
	1,
	0
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'outputs',
	'X_VlcShares_Plugins_Outputs', 
	'X/VlcShares/Plugins/Outputs.php',
	'p_outputs_plglabel',
	'p_outputs_plgdesc',
	1,
	0
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'fileSubs',
	'X_VlcShares_Plugins_FileSubs', 
	'X/VlcShares/Plugins/FileSubs.php',
	'p_filesubs_plglabel',
	'p_filesubs_plgdesc',
	1,
	1
);

-- filesubs need extra lines in configs


INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.file.enabled','1','1','plugins',3,'p_filesubs_conf_fileenabled_label','p_filesubs_conf_fileenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.file.extensions','srt|sub|txt|idx','srt|sub|txt|idx','plugins',0,'p_filesubs_conf_fileextensions_label','p_filesubs_conf_fileextensions_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.infile.enabled','1','1','plugins',3,'p_filesubs_conf_infileenabled_label','p_filesubs_conf_infileenabled_desc','');


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'hideHidden',
	'X_VlcShares_Plugins_HideHidden', 
	'X/VlcShares/Plugins/HideHidden.php',
	'p_hidehidden_plglabel',
	'p_hidehidden_plgdesc',
	1,
	1
);


INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'hideHidden.hideSystem', '1', '1', 'plugins', 3, 'p_hidehidden_conf_hidesystem_label', 'p_hidehidden_conf_hidesystem_desc',	'');


-- hidehidden need extra line in configs

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'filterExt',
	'X_VlcShares_Plugins_FilterExt', 
	'X/VlcShares/Plugins/FilterExt.php',
	'p_filterext_plglabel',
	'p_filterext_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'filterExt.valid', 'avi|mkv|mpg|mpeg|mov|3gp|mp4|mp3|mp2|ts|mpv|mpa|mpgv|mpga|divx|dvx|flv', 'avi|mkv|mpg|mpeg|mov|3gp|mp4|mp3|mp2|ts|mpv|mpa|mpgv|mpga|divx|dvx|flv', 'plugins', 0, 'p_filterext_conf_valid_label', 'p_filterext_conf_valid_desc',	'');



INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'sortItems',
	'X_VlcShares_Plugins_SortItems', 
	'X/VlcShares/Plugins/SortItems.php',
	'p_sortitems_plglabel',
	'p_sortitems_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'wiimc',
	'X_VlcShares_Plugins_WiimcPlxRenderer', 
	'X/VlcShares/Plugins/WiimcPlxRenderer.php',
	'p_wiimcplxrenderer_plglabel',
	'p_wiimcplxrenderer_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.enabled', '0', '0', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedenabled_label', 'p_wiimcplxrenderer_conf_forcedenabled_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.fancy', '1', '1', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedfancy_label', 'p_wiimcplxrenderer_conf_forcedfancy_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.showRaw', '0', '0', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedshowraw_label', 'p_wiimcplxrenderer_conf_forcedshowraw_desc',	'');


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'mobilerenderer',
	'X_VlcShares_Plugins_MobileRenderer', 
	'X/VlcShares/Plugins/MobileRenderer.php',
	'p_mobilerenderer_plglabel',
	'p_mobilerenderer_plgdesc',
	1,
	1
);
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mobilerenderer.forced.enabled', '1', '0', 'plugins', 3, 'p_mobilerenderer_conf_forcedenabled_label', 'p_mobilerenderer_conf_forcedenabled_desc',	'');

	

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'redirectControls',
	'X_VlcShares_Plugins_RedirectControls', 
	'X/VlcShares/Plugins/RedirectControls.php',
	'p_redirectcontrols_plglabel',
	'p_redirectcontrols_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'controls',
	'X_VlcShares_Plugins_Controls', 
	'X/VlcShares/Plugins/Controls.php',
	'p_controls_plglabel',
	'p_controls_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.pauseresume.enabled', '0', '0', 'plugins', 3, 'p_controls_conf_pauseresumeenabled_label', 'p_controls_conf_pauseresumeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.stop.enabled', '1', '1', 'plugins', 3, 'p_controls_conf_stopenabled_label', 'p_controls_conf_stopenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.forwardrelative.enabled', '0', '0', 'plugins', 3, 'p_controls_conf_forwardrelativeenabled_label', 'p_controls_conf_forwardrelativeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.backrelative.enabled', '0', '0', 'plugins', 3, 'p_controls_conf_backrelativeenabled_label', 'p_controls_conf_backrelativeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.seek.enabled', '1', '1', 'plugins', 3, 'p_controls_conf_seekenabled_label', 'p_controls_conf_seekenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.oldstylecontrols.enabled', '0', '0', 'plugins', 3, 'p_controls_conf_oldstylecontrolsenabled_label', 'p_controls_conf_oldstylecontrolsenabled_desc', '');


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'workaround_wiimc1',
	'X_VlcShares_Plugins_WorkaroundWiimcPlaylistItemsBug', 
	'X/VlcShares/Plugins/WorkaroundWiimcPlaylistItemsBug.php',
	'p_workaroundwiimcplaylistitemsbug_plglabel',
	'p_workaroundwiimcplaylistitemsbug_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'workaround_vlc1',
	'X_VlcShares_Plugins_WorkaroundVlc12ParsePathBug', 
	'X/VlcShares/Plugins/WorkaroundVlc12ParsePathBug.php',
	'p_workaroundvlc12parsepathbug_plglabel',
	'p_workaroundvlc12parsepathbug_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'coreStats',
	'X_VlcShares_Plugins_CoreStats', 
	'X/VlcShares/Plugins/CoreStats.php',
	'p_corestats_plglabel',
	'p_corestats_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'widgetDevAlert',
	'X_VlcShares_Plugins_WidgetDevAlert', 
	'X/VlcShares/Plugins/WidgetDevAlert.php',
	'p_widgetdevalert_plglabel',
	'p_widgetdevalert_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'streaminfo',
	'X_VlcShares_Plugins_StreamInfo', 
	'X/VlcShares/Plugins/StreamInfo.php',
	'p_streaminfo_plglabel',
	'p_streaminfo_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'streaminfo.show.title', '1', '1', 'plugins', 3, 'p_streaminfo_conf_showtitle_label', 'p_streaminfo_conf_showtitle_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'streaminfo.show.time', '0', '0', 'plugins', 3, 'p_streaminfo_conf_showtime_label', 'p_streaminfo_conf_showtime_desc', '');

	
INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'dbforever',
	'X_VlcShares_Plugins_DBForever', 
	'X/VlcShares/Plugins/DBForever.php',
	'p_dbforever_plglabel',
	'p_dbforever_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.index.url', 'http://www.dbforever.net/home.php', 'http://www.dbforever.net/home.php', 'plugins', 0, 'p_dbforever_conf_indexurl_label', 'p_dbforever_conf_indexurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.request.timeout', '10', '10', 'plugins', 0, 'p_dbforever_conf_requesttimeout_label', 'p_dbforever_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'dbforever.request.maxredirects', '0', '0', 'plugins', 0, 'p_dbforever_conf_requestredirects_label', 'p_dbforever_conf_requestredirects_desc', '');

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'animeland',
	'X_VlcShares_Plugins_AnimeLand', 
	'X/VlcShares/Plugins/AnimeLand.php',
	'p_animeland_plglabel',
	'p_animeland_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.base.url', 'http://www.animeland.it/', 'http://www.animeland.it/', 'plugins', 0, 'p_animeland_conf_baseurl_label', 'p_animeland_conf_baseurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.index.page', 'menu_streaming.html', 'menu_streaming.html', 'plugins', 0, 'p_animeland_conf_indexpage_label', 'p_animeland_conf_indexpage_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.request.timeout', '10', '10', 'plugins', 0, 'p_animeland_conf_requesttimeout_label', 'p_animeland_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'animeland.request.maxredirects', '0', '0', 'plugins', 0, 'p_animeland_conf_requestredirects_label', 'p_animeland_conf_requestredirects_desc', '');

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'southpark',
	'X_VlcShares_Plugins_SouthPark', 
	'X/VlcShares/Plugins/SouthPark.php',
	'p_southpark_plglabel',
	'p_southpark_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'southpark.request.timeout', '10', '10', 'plugins', 0, 'p_southpark_conf_requesttimeout_label', 'p_southpark_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'southpark.request.maxredirects', '0', '0', 'plugins', 0, 'p_southpark_conf_requestredirects_label', 'p_southpark_conf_requestredirects_desc', '');

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'narutoget',
	'X_VlcShares_Plugins_NarutoGet', 
	'X/VlcShares/Plugins/NarutoGet.php',
	'p_narutoget_plglabel',
	'p_narutoget_plgdesc',
	1,
	1
);

/*
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.index.naruto.url', 'http://www.narutoget.com/page/10-naruto-episodes-subbed/', 'http://www.narutoget.com/page/10-naruto-episodes-subbed/', 'plugins', 0, 'p_narutoget_conf_indexnarutourl_label', 'p_narutoget_conf_indexnarutourl_desc', '');
*/	
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.index.shippuden.url', 'http://www.narutoget.com/naruto-shippuden-episodes/', 'http://www.narutoget.com/naruto-shippuden-episodes/', 'plugins', 0, 'p_narutoget_conf_indexshippudenurl_label', 'p_narutoget_conf_indexshippudenurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.request.timeout', '10', '10', 'plugins', 0, 'p_animeland_conf_requesttimeout_label', 'p_animeland_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'narutoget.request.maxredirects', '0', '0', 'plugins', 0, 'p_animeland_conf_requestredirects_label', 'p_animeland_conf_requestredirects_desc', '');
	
INSERT INTO plugins ( `key`, `class`, `file`, `enabled`, `type` ) VALUES (
	'firstrunsetup',
	'X_VlcShares_Plugins_FirstRunSetup', 
	'X/VlcShares/Plugins/FirstRunSetup.php',
	1,
	0
);
	
INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'opfitalia',
	'X_VlcShares_Plugins_OPFItalia', 
	'X/VlcShares/Plugins/OPFItalia.php',
	'p_opfitalia_plglabel',
	'p_opfitalia_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.index.url', 'http://www.opfitalia.net/mediacenter/streaming.php', 'http://www.opfitalia.net/mediacenter/streaming.php', 'plugins', 0, 'p_opfitalia_conf_indexurl_label', 'p_opfitalia_conf_indexurl_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.request.timeout', '10', '10', 'plugins', 0, 'p_opfitalia_conf_requesttimeout_label', 'p_opfitalia_conf_requesttimeout_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'opfitalia.request.maxredirects', '0', '0', 'plugins', 0, 'p_opfitalia_conf_requestredirects_label', 'p_opfitalia_conf_requestredirects_desc', '');

