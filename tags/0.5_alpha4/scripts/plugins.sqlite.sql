

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'fileSystemBrowser',
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
	'p_silesubs_plgdesc',
	1,
	1
);

-- filesubs need extra lines in configs


INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.file.enabled','1','1','plugins',2,'p_filesubs_conf_fileenabled_label','p_filesubs_conf_fileenabled_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.file.extensions','srt|sub|txt|idx','srt|sub|txt|idx','plugins',0,'p_filesubs_conf_fileextensions_label','p_filesubs_conf_fileextensions_desc','');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fileSubs.infile.enabled','1','1','plugins',2,'p_filesubs_conf_infileenabled_label','p_filesubs_conf_infileenabled_desc','');


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
	'hideHidden.hideSystem', '1', '1', 'plugins', 2, 'p_hidehidden_conf_hidesystem_label', 'p_hidehidden_conf_hidesystem_desc',	'');


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
	'wiimc.forced.enabled', '1', '0', 'plugins', 2, 'p_wiimcplxrenderer_conf_forcedenabled_label', 'p_wiimcplxrenderer_conf_forcedenabled_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.fancy', '1', '0', 'plugins', 2, 'p_wiimcplxrenderer_conf_forcedfancy_label', 'p_wiimcplxrenderer_conf_forcedfancy_desc',	'');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.showRaw', '1', '0', 'plugins', 2, 'p_wiimcplxrenderer_conf_forcedshowraw_label', 'p_wiimcplxrenderer_conf_forcedshowraw_desc',	'');



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
	'controls.pauseresume.enabled', '0', '0', 'plugins', 2, 'p_controls_conf_pauseresumeenabled_label', 'p_controls_conf_pauseresumeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.stop.enabled', '1', '1', 'plugins', 2, 'p_controls_conf_stopenabled_label', 'p_controls_conf_stopenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.forwardrelative.enabled', '0', '0', 'plugins', 2, 'p_controls_conf_forwardrelativeenabled_label', 'p_controls_conf_forwardrelativeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.backrelative.enabled', '0', '0', 'plugins', 2, 'p_controls_conf_backrelativeenabled_label', 'p_controls_conf_backrelativeenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.seek.enabled', '1', '1', 'plugins', 2, 'p_controls_conf_seekenabled_label', 'p_controls_conf_seekenabled_desc', '');
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'controls.oldstylecontrols.enabled', '0', '0', 'plugins', 2, 'p_controls_conf_oldstylecontrolsenabled_label', 'p_controls_conf_oldstylecontrolsenabled_desc', '');


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

