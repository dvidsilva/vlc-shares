
CREATE TABLE plg_filesystem_shares (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	path VARCHAR(255) NOT NULL DEFAULT '',
	image VARCHAR(255) DEFAULT NULL
);

CREATE INDEX "plg_filesystem_shares_id" ON "plg_filesystem_shares" ("id");


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


CREATE TABLE plg_outputs (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	arg TEXT DEFAULT NULL,
	link TEXT DEFAULT NULL,
	cond_devices INT DEFAULT NULL,
	weight INT DEFAULT NULL
);

CREATE INDEX "plg_outputs_id" ON "plg_outputs" ("id");


CREATE TABLE configs (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`key` VARCHAR(255) NOT NULL UNIQUE,
	`value` TEXT DEFAULT NULL,
	`default` TEXT DEFAULT NULL,
	section VARCHAR(255) NOT NULL DEFAULT "general",
	label VARCHAR(255) DEFAULT NULL,
	description VARCHAR(255) DEFAULT NULL,
	`type` INTEGER DEFAULT 0,
	class VARCHAR(255) DEFAULT NULL
);

CREATE INDEX "configs_id" ON "configs" ("id");


CREATE TABLE plugins (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`key` VARCHAR(255) NOT NULL UNIQUE,
	class VARCHAR(255) NOT NULL,
	file VARCHAR(255) DEFAULT NULL,
	label VARCHAR(255) DEFAULT NULL,
	description VARCHAR(255) DEFAULT NULL,
	`type` INTEGER NOT NULL DEFAULT 0,
	enabled INTEGER NOT NULL DEFAULT 0,
	version VARCHAR(16) DEFAULT NULL
);

CREATE INDEX "plugins_id" ON "plugins" ("id");


CREATE TABLE plg_cache (
	uri TEXT NOT NULL PRIMARY KEY,
	content BLOB DEFAULT NULL,
	cType INTEGER NOT NULL DEFAULT 0,
	created INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX "plg_cache_uri" ON "plg_cache" ("uri");
