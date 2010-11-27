
DROP TABLE IF EXISTS plg_youtube_accounts;

DROP TABLE IF EXISTS plg_youtube_categories;

DROP TABLE IF EXISTS plg_youtube_videos;

DELETE FROM configs WHERE `key` = 'youtube.closedcaption.enabled';
DELETE FROM configs WHERE `key` = 'youtube.quality.priority';
