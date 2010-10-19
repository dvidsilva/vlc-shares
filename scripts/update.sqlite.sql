
-- UPDATE DATABASE FROM 0.5 -> 0.5.1

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'mobilerenderer.coverflow.enabled', '1', '1', 'plugins', 3, 'p_mobilerenderer_conf_coverflowenabled_label', 'p_mobilerenderer_conf_coverflowenabled_desc',	'');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'wiimc.forced.showThumbs', '1', '1', 'plugins', 3, 'p_wiimcplxrenderer_conf_forcedshowthumbs_label', 'p_wiimcplxrenderer_conf_forcedshowthumbs_desc',	'');


-- Update plg_profiles.arg type from varchar255 -> text -- [sqlite don't allow column rename or remove]
ALTER TABLE plg_profiles RENAME TO plg_profiles_back;

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

INSERT INTO plg_profiles (id, label, arg, cond_devices, cond_providers, cond_formats, weight) SELECT * FROM plg_profiles_back;

DROP TABLE IF EXISTS plg_profiles_back;

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

