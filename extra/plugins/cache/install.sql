
CREATE TABLE plg_cache (
	uri TEXT NOT NULL PRIMARY KEY,
	content BLOB DEFAULT NULL,
	cType INTEGER NOT NULL DEFAULT 0,
	created INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX "plg_cache_uri" ON "plg_cache" ("uri");

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'cache.validity','60','60','plugins',0,'p_cache_conf_validity_label','p_cache_conf_validity_desc','');

INSERT INTO configs ( `key`, `value`, `default`, `section`, type, label, description, class ) VALUES (
	'cache.refresh.allowed','1','1','plugins',3,'p_cache_conf_refreshallowed_label','p_cache_conf_refreshallowed_desc','');
	
UPDATE plugins SET enabled=1 WHERE key = "cache";

