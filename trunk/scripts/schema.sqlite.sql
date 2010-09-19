CREATE TABLE plg_megavideo (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    idVideo VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'default',
    label VARCHAR(255) NOT NULL,
    description TEXT NULL
);
 
CREATE INDEX "plg_megavideo_id" ON "plg_megavideo" ("id");


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
	arg VARCHAR(255) DEFAULT NULL,
	cond_devices INT DEFAULT NULL,
	cond_providers VARCHAR(255) DEFAULT NULL,
	cond_formats VARCHAR(255) DEFAULT NULL,
	weight INT DEFAULT NULL
);

CREATE INDEX "plg_profiles_id" ON "plg_profiles" ("id");

CREATE TABLE plg_outputs (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	arg VARCHAR(255) DEFAULT NULL,
	link VARCHAR(255) DEFAULT NULL,
	cond_devices INT DEFAULT NULL,
	weight INT DEFAULT NULL
);

CREATE INDEX "plg_outputs_id" ON "plg_outputs" ("id");


