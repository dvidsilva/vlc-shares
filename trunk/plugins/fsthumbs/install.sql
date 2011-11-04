CREATE TABLE plg_fsthumbs (
	path TEXT NOT NULL PRIMARY KEY,
	url TEXT NOT NULL,
	size INTEGER NOT NULL DEFAULT 0,
	created INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX "plg_fsthumbs_path" ON "plg_fsthumbs" ("path");

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fsthumbs.thumbs.size','320x240','320x240','plugins',0,'p_fsthumbs_conf_thumbssize_label','p_fsthumbs_conf_thumbssize_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fsthumbs.capture.seconds','2','2','plugins',0,'p_fsthumbs_conf_captureseconds_label','p_fsthumbs_conf_captureseconds_desc','');
	
INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'fsthumbs.max.cached','200','200','plugins',0,'p_fsthumbs_conf_maxcached_label','p_fsthumbs_conf_maxcached_desc','');
	
UPDATE plugins SET enabled=1 WHERE key = "fsthumbs";

