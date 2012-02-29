INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'auth',
	'X_VlcShares_Plugins_Auth', 
	'X/VlcShares/Plugins/Auth.php',
	'p_auth_plglabel',
	'p_auth_plgdesc',
	0,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'plugininstaller',
	'X_VlcShares_Plugins_PluginInstaller', 
	'X/VlcShares/Plugins/PluginInstaller.php',
	'p_plugininstaller_plglabel',
	'p_plugininstaller_plgdesc',
	1,
	1
);

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
	'onlinelibrary',
	'X_VlcShares_Plugins_OnlineLibrary', 
	'X/VlcShares/Plugins/OnlineLibrary.php',
	'p_onlinelibrary_plglabel',
	'p_onlinelibrary_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'rtmpdump',
	'X_VlcShares_Plugins_RtmpDump', 
	'X/VlcShares/Plugins/RtmpDump.php',
	'p_rtmpdump_plglabel',
	'p_rtmpdump_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'sopcast',
	'X_VlcShares_Plugins_SopCast', 
	'X/VlcShares/Plugins/SopCast.php',
	'p_sopcast_plglabel',
	'p_sopcast_plgdesc',
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
	'filterExt.valid', 'avi|mkv|mpg|mpeg|mov|3gp|mp4|mp3|mp2|ts|mpv|mpa|mpgv|mpga|divx|dvx|flv|wmv|wma', 'avi|mkv|mpg|mpeg|mov|3gp|mp4|mp3|mp2|ts|mpv|mpa|mpgv|mpga|divx|dvx|flv|wmv|wma', 'plugins', 0, 'p_filterext_conf_valid_label', 'p_filterext_conf_valid_desc',	'');



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
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.showThumbs', '1', '1', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedshowthumbs_label', 'p_wiimcplxrenderer_conf_forcedshowthumbs_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.support.enhanced', '1', '1', 'plugins', 3, 'p_wiimcplxrenderer_conf_supportenhanced_label', 'p_wiimcplxrenderer_conf_supportenhanced_desc',	'');

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

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mobilerenderer.coverflow.enabled', '1', '1', 'plugins', 3, 'p_mobilerenderer_conf_coverflowenabled_label', 'p_mobilerenderer_conf_coverflowenabled_desc',	'');

	

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
	0,
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
	'streaminfo.show.time', '1', '1', 'plugins', 3, 'p_streaminfo_conf_showtime_label', 'p_streaminfo_conf_showtime_desc', '');

	
INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'firstrunsetup',
	'X_VlcShares_Plugins_FirstRunSetup', 
	'X/VlcShares/Plugins/FirstRunSetup.php',
	'p_firstrunsetup_plglabel',
	'p_firstrunsetup_plgdesc',
	1,
	1
);
	
INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'widgetDevNews',
	'X_VlcShares_Plugins_WidgetDevNews', 
	'X/VlcShares/Plugins/WidgetDevNews.php',
	'p_widgetdevnews_plglabel',
	'p_widgetdevnews_plgdesc',
	0,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'backupper',
	'X_VlcShares_Plugins_Backupper', 
	'X/VlcShares/Plugins/Backupper.php',
	'p_backupper_plglabel',
	'p_backupper_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'emptylists',
	'X_VlcShares_Plugins_EmptyLists', 
	'X/VlcShares/Plugins/EmptyLists.php',
	'p_emptylists_plglabel',
	'p_emptylists_plgdesc',
	1,
	1
);



INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'cache',
	'X_VlcShares_Plugins_Cache', 
	'X/VlcShares/Plugins/Cache.php',
	'p_cache_plglabel',
	'p_cache_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'cache.validity','60','60','plugins',0,'p_cache_conf_validity_label','p_cache_conf_validity_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'cache.refresh.allowed','1','1','plugins',3,'p_cache_conf_refreshallowed_label','p_cache_conf_refreshallowed_desc','');


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'updatenotifier',
	'X_VlcShares_Plugins_UpdateNotifier', 
	'X/VlcShares/Plugins/UpdateNotifier.php',
	'p_updatenotifier_plglabel',
	'p_updatenotifier_plgdesc',
	1,
	1
);

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.autocheck.delay','1','1',
	'plugins',0,'p_updatenotifier_conf_autocheckdelay_label','p_updatenotifier_conf_autocheckdelay_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.autocheck.last','','',
	'plugins',0,'p_updatenotifier_conf_autochecklast_label','p_updatenotifier_conf_autochecklast_desc','hidden');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.core.stable.index','http://vlc-shares.googlecode.com/svn/updates/core/STABLE.xml','http://vlc-shares.googlecode.com/svn/updates/core/STABLE.xml',
	'plugins',0,'p_updatenotifier_conf_corestableindex_label','p_updatenotifier_conf_corestableindex_desc','');
	
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.core.unstable.index','http://vlc-shares.googlecode.com/svn/updates/core/UNSTABLE.xml','http://vlc-shares.googlecode.com/svn/updates/core/UNSTABLE.xml',
	'plugins',0,'p_updatenotifier_conf_coreunstableindex_label','p_updatenotifier_conf_coreunstableindex_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.plugins.index','http://vlc-shares.googlecode.com/svn/updates/plugins/INDEX.xml','http://vlc-shares.googlecode.com/svn/updates/plugins/INDEX.xml',
	'plugins',0,'p_updatenotifier_conf_pluginsindex_label','p_updatenotifier_conf_pluginsindex_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.core.allow.unstable','0','0',
	'plugins',3,'p_updatenotifier_conf_coreallowunstable_label','p_updatenotifier_conf_coreallowunstable_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'updatenotifier.plugins.allow.unstable','0','0',
	'plugins',3,'p_updatenotifier_conf_pluginsallowunstable_label','p_updatenotifier_conf_pluginsallowunstable_desc','');


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'audioSwitcher',
	'X_VlcShares_Plugins_AudioSwitcher', 
	'X/VlcShares/Plugins/AudioSwitcher.php',
	'p_audioswitcher_plglabel',
	'p_audioswitcher_plgdesc',
	1,
	1
);

-- audioSwitcher need extra lines in configs

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.file.enabled','1','1','plugins',3,'p_audioswitcher_conf_fileenabled_label','p_audioswitcher_conf_fileenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.file.extensions','mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3','mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3','plugins',0,'p_audioswitcher_conf_fileextensions_label','p_audioswitcher_conf_fileextensions_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'audioSwitcher.infile.enabled','1','1','plugins',3,'p_audioswitcher_conf_infileenabled_label','p_audioswitcher_conf_infileenabled_desc','');

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'bookmarklets',
	'X_VlcShares_Plugins_Bookmarklets', 
	'X/VlcShares/Plugins/Bookmarklets.php',
	'p_bookmarklets_plglabel',
	'p_bookmarklets_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'devices',
	'X_VlcShares_Plugins_Devices', 
	'X/VlcShares/Plugins/Devices.php',
	'p_devices_plglabel',
	'p_devices_plgdesc',
	1,
	1
);


INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'webkitrenderer',
	'X_VlcShares_Plugins_WebkitRenderer', 
	'X/VlcShares/Plugins/WebkitRenderer.php',
	'p_webkitrenderer_plglabel',
	'p_webkitrenderer_plgdesc',
	1,
	1
);

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'upnprenderer',
	'X_VlcShares_Plugins_UpnpRenderer', 
	'X/VlcShares/Plugins/UpnpRenderer.php',
	'p_upnp_plglabel',
	'p_upnp_plgdesc',
	1,
	1
);

