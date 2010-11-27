

CREATE TABLE plg_youtube_accounts (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    label VARCHAR(255) NOT NULL,
    thumbnail TEXT NULL
);

CREATE TABLE plg_youtube_categories (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    label VARCHAR(255) NOT NULL,
    thumbnail TEXT NULL
);

CREATE TABLE plg_youtube_videos (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    idCategory INTEGER NOT NULL,
    label VARCHAR(255) NOT NULL,
    idYoutube VARCHAR(255) NOT NULL,
    thumbnail TEXT NULL,
    description TEXT NULL
);

INSERT INTO plg_youtube_accounts VALUES
	(NULL, 'ximarx', 'http://s.ytimg.com/yt/img/no_videos_140-vfl1fDI7-.png' );
	
INSERT INTO plg_youtube_categories VALUES
	(NULL, 'VLCShares', '/images/youtube/uploads/folder.png' );	
	
INSERT INTO plg_youtube_videos VALUES
	(NULL, 1, 'VLCShares - Megavideo library and bookmarklets', 'GycWEv7cWLk', 'http://i.ytimg.com/vi/GycWEv7cWLk/0.jpg', 'The video show how to use the megavideo plugin of VLCShares and its bookmarklets feature. More infos about VLCShares: http://code.google.com/p/vlc-shares/' );
	
UPDATE plugins SET enabled=1 WHERE key = "youtube";

