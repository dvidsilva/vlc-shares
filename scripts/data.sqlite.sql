
-- scripts/data.sqlite.sql
--
-- You can begin populating the database with the following SQL statements.
 
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('NBDB38CE',
    'BBT',
    'PILOT',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('ZUWSN1B9',
    'BBT',
    'puntata 1',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('T65OW4BR',
    'American Dad',
    'puntata X',
    '');
    
    
INSERT INTO plg_filesystem_shares (label, path ) VALUES
	('Root windows',
	'C:\'
	);

	
INSERT INTO plg_filesystem_shares (label, path) VALUES
	('Root linux',
	'/'
	);
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Hq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=4000,scale=.5,width=640,fps=30,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}:std{access=http,mux=ts,dst=:8081}'
	);	
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Mq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=3000,scale=.5,width=640,fps=30,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}:std{access=http,mux=ts,dst=:8081}'
	);
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Lq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=2000,scale=.5,width=640,fps=30,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}:std{access=http,mux=ts,dst=:8081}'
	);		