
-- UPDATE DATABASE FROM 0.5 -> 0.5.1

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mobilerenderer.coverflow.enabled', '1', '1', 'plugins', 3, 'p_mobilerenderer_conf_coverflowenabled_label', 'p_mobilerenderer_conf_coverflowenabled_desc',	'');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.showThumbs', '1', '1', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedshowthumbs_label', 'p_wiimcplxrenderer_conf_forcedshowthumbs_desc',	'');


-- Update plg_profiles.arg type from varchar255 -> text -- [sqlite don't allow column rename or remove]
DROP TABLE IF EXISTS plg_profiles;
DROP INDEX IF EXISTS "plg_profiles_id";

CREATE TABLE plg_profiles (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	arg TEXT DEFAULT NULL,
	cond_devices INT DEFAULT NULL,
	cond_providers VARCHAR(255) DEFAULT NULL,
	cond_formats VARCHAR(255) DEFAULT NULL,
	weight INT DEFAULT 0
);

CREATE INDEX "plg_profiles_id" ON "plg_profiles" ("id");

-- Insert new profiles for 0.5.1

INSERT INTO plg_profiles (label, arg) VALUESversion VARCHAR(16) DEFAULT NULL
	('Hq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=4000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);	
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Mq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=3000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Lq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=2000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);
	
INSERT INTO plg_profiles (label, arg, cond_formats) VALUES
	('AVC/ACC safe profile',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=1000,scale=.5,width=640,fps=25,soverlay}',
	'h264+aac');

INSERT INTO plg_profiles (label, arg, cond_devices, weight) VALUES
	('Android Phone (HQ)',
	'transcode{vcodec=h264,venc=x264{no-cabac,level=12,vbv-maxrate=384,vbv-bufsize=1000,keyint=75,ref=3,bframes=0},width=320,height=180,acodec=mp4a,ab=64,vb=384}',
	1,
	1);

INSERT INTO plg_profiles (label, arg, cond_devices, cond_formats) VALUES
	('Android Phone (AVC/AAC)',
	'transcode{vcodec=h264,venc=x264{no-cabac,level=12,vbv-maxrate=384,vbv-bufsize=1000,keyint=75,ref=3,bframes=0},width=320,height=180,vb=384}',
	1,
	'h264+aac');
	
	
INSERT INTO plg_profiles (label, arg, cond_devices) VALUES
	('Android Phone (LQ)',
	'transcode{vcodec=mp4v,vb=400,fps=25,scale=0.5,acodec=mp4a,ab=20,channels=2}',
	1);

-- Update p_outputs.arg type from varchar255 -> text -- [sqlite don't allow column rename or remove]
ALTER TABLE plg_outputs RENAME TO plg_outputs_back;

DROP INDEX IF EXISTS "plg_outputs_id";

CREATE TABLE plg_outputs (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	arg TEXT DEFAULT NULL,
	link TEXT DEFAULT NULL,
	cond_devices INT DEFAULT NULL,
	weight INT DEFAULT NULL
);

CREATE INDEX "plg_outputs_id" ON "plg_outputs" ("id");

INSERT INTO plg_outputs (id, label, arg, link, cond_devices, weight) SELECT * FROM plg_outputs_back;

DROP TABLE IF EXISTS plg_outputs_back;

-- Add new WidgetDevNews Plugin

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'widgetDevNews',
	'X_VlcShares_Plugins_WidgetDevNews', 
	'X/VlcShares/Plugins/WidgetDevNews.php',
	'p_widgetdevnews_plglabel',
	'p_widgetdevnews_plgdesc',
	1,
	1
);

-- Add new Backupper Plugin

INSERT INTO plugins ( `key`, `class`, `file`, `label`, `description`, `enabled`, `type` ) VALUES (
	'backupper',
	'X_VlcShares_Plugins_Backupper', 
	'X/VlcShares/Plugins/Backupper.php',
	'p_backupper_plglabel',
	'p_backupper_plgdesc',
	1,
	1
);

-- Add new column to plugin table

ALTER TABLE plugins ADD COLUMN version VARCHAR(16) DEFAULT NULL;
