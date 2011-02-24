
-- scripts/data.sqlite.sql
--
-- You can begin populating the database with the following SQL statements.
 
    
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
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=4000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);	
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Mq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=3000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);
	
INSERT INTO plg_profiles (label, arg) VALUES
	('Lq',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=2000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}'
	);
	
INSERT INTO plg_profiles (label, arg, cond_formats) VALUES
	('AVC/ACC safe profile',
	'transcode{venc=ffmpeg,vcodec=mp2v,vb=1000,scale=.5,width=640,fps=25,soverlay}',
	'h264+aac');

INSERT INTO plg_profiles (label, arg, cond_devices, weight) VALUES
	('Android Phone (HQ)',
	'transcode{vcodec=h264,venc=x264{no-cabac,level=12,vbv-maxrate=384,vbv-bufsize=1000,keyint=75,ref=3,bframes=0},width=320,height=180,acodec=mp4a,ab=64,vb=384}',
	1,
	1);

INSERT INTO plg_profiles (label, arg, cond_devices, cond_formats) VALUES
	('Android Phone (AVC/AAC)',
	'transcode{vcodec=h264,venc=x264{no-cabac,level=12,vbv-maxrate=384,vbv-bufsize=1000,keyint=75,ref=3,bframes=0},width=320,height=180,vb=384}',
	1,
	'h264+aac');
	
	
INSERT INTO plg_profiles (label, arg, cond_devices) VALUES
	('Android Phone (LQ)',
	'transcode{vcodec=mp4v,vb=400,fps=25,scale=0.5,acodec=mp4a,ab=20,channels=2}',
	1);
	
	
INSERT INTO plg_outputs (label, arg, link, cond_devices) VALUES
	('Http stream on 8081',
	'std{access=http,mux=ts,dst=:8081}',
	'http://{%SERVER_IP%}:8081/',
	NULL);	
	
INSERT INTO plg_outputs (label, arg, link, cond_devices) VALUES
	('WIIMC stream',
	'std{access=http,mux=ts,dst=:8081}',
	'http://{%SERVER_IP%}:8081/',
	0);	
	
INSERT INTO plg_outputs (label, arg, link, cond_devices) VALUES
	('Simulated WIIMC stream for PC',
	'std{access=http,mux=ts,dst=:8081}',
	'http://{%SERVER_IP%}:8081/',
	100);	
	
INSERT INTO plg_outputs (label, arg, link, cond_devices) VALUES
	('Android Phone (Rtp Stream)',
	'rtp{mp4a-latm,sdp=rtsp://0.0.0.0:5554/android.sdp}',
	'rtsp://{%SERVER_IP%}:5554/android.sdp',
	1);
	