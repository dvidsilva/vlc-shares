UPDATE plugins SET enabled=1 WHERE key = "hulu";

UPDATE configs SET value = "3" WHERE key = "debug.level";
UPDATE configs SET value = "1" WHERE key = "debug.enabled";