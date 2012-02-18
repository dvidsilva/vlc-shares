-- Change configuration for dev
UPDATE configs SET value="1" WHERE key = "debug.enabled";
UPDATE configs SET value="3" WHERE key = "debug.level";
UPDATE configs SET value="1" WHERE key = "threads.logger";
UPDATE configs SET value="1" WHERE key = "adapter.log";

UPDATE configs SET value="X_VlcShares_Plugins_MobileRenderer" WHERE key = "devices.gui";

UPDATE configs SET value="1" WHERE key = "updatenotifier.core.allow.unstable";
UPDATE configs SET value="1" WHERE key = "updatenotifier.plugins.allow.unstable";

-- Disable first-installation for dev
UPDATE plugins SET enabled="0" WHERE key = "firstrunsetup";

-- Disable dev-news for dev
UPDATE plugins SET enabled="0" WHERE key = "widgetDevNews";

-- Disable update-notifier for dev
UPDATE plugins SET enabled="0" WHERE key = "updatenotifier";

-- Disable update-notifier for dev
UPDATE plugins SET enabled="0" WHERE key = "widgetDevAlert";

-- Disable upnp-renderer (not ready yet)
UPDATE plugins SET enabled="0" WHERE key = "upnprenderer";

-- Add fake admin account (user: admin, password: admin)
INSERT INTO plg_auth_accounts ("username", "password", "passphrase", "enabled", "altAllowed") VALUES 
(
	'admin',
	'd2abaa37a7c3db1137d385e1d8c15fd2',
	'bd538236c4924c90cbb62f979b20b9ab',
	'1',
	'1'
);

-- Add a fake device to catch all user-agent in mobile-renderer
INSERT INTO plg_devices ("label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
(
	'DEBUG FAKE DEVICE',
	'/.*/',
	'0',
	'6',
	'X_VlcShares_Plugins_MobileRenderer',
	'a:1:{s:12:"alt-profiles";a:5:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"5";i:3;s:1:"6";i:4;s:1:"7";}}',
	'100'
);

