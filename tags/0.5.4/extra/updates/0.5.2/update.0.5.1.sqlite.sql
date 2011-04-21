
-- Fix a bug in 0.5 -> 0.5.1 update
UPDATE plugins SET `key` = 'fileSystem' WHERE `key` = 'fileSystemBrowser';
-- Fix regression in 0.5.1
UPDATE configs SET `label` = 'config_general_debug_level_label', `description` = 'config_general_debug_level_desc' WHERE `key` = 'debug.level';