DELETE FROM configs WHERE `key` LIKE 'hulu.%';

UPDATE configs SET value = "1" WHERE key = "debug.level";
UPDATE configs SET value = "0" WHERE key = "debug.enabled";
