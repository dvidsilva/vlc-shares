-- scripts/data.sqlite.sql
--
-- You can begin populating the database with the following SQL statements.
 
-- DEFAULT SHARES

INSERT INTO plg_filesystem_shares (label, path ) VALUES
	('Root windows',
	'C:\'
	);

	
INSERT INTO plg_filesystem_shares (label, path) VALUES
	('Root linux',
	'/'
	);
	
	
-- PROFILES
	
-- 1
INSERT INTO plg_profiles (label, arg, link) VALUES
	('MP2v/AC3 over HTTP(ts)',
	'#transcode{venc=ffmpeg,vcodec=mp2v,vb=3000,scale=.5,width=640,fps=25,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}:std{access=http{mime=video/mpeg},mux=ts,dst=0.0.0.0:8081}',
	'http://{%SERVER_NAME%}:8081/'
	);

-- 2
INSERT INTO plg_profiles (label, arg, link) VALUES
	('MP2v/-original- over HTTP(ts)',
	'#transcode{venc=ffmpeg,vcodec=mp2v,vb=3000,scale=.5,width=640,fps=25,soverlay}:std{access=http{mime=video/mpeg},mux=ts,dst=0.0.0.0:8081}',
	'http://{%SERVER_NAME%}:8081/'
	);

-- 3
INSERT INTO plg_profiles (label, arg, link) VALUES
	('H264/AAC over RTSP',
	'#transcode{vcodec=h264,venc=x264{no-cabac,level=12,vbv-maxrate=384,vbv-bufsize=1000,keyint=75,ref=3,bframes=0},width=320,height=180,acodec=mp4a,ab=64,vb=384}:rtp{mp4a-latm,sdp=rtsp://0.0.0.0:5554/android.sdp}',
	'rtsp://{%SERVER_NAME%}:5554/android.sdp'
	);

-- 4
INSERT INTO plg_profiles (label, arg, link) VALUES
	('H264/AAC (HQ) over RTSP',
	'#transcode{vcodec=h264,venc=x264{no-cabac,keyint=75,ref=3,bframes=0},width=800,vb=1200,profile=baseline,level=1.2,acodec=mp4a,ab=160,channels=2}:rtp{mp4a-latm,sdp=rtsp://0.0.0.0:5554/android.sdp}',
	'rtsp://{%SERVER_NAME%}:5554/android.sdp'
	);

-- 5	
INSERT INTO plg_profiles (label, arg, link) VALUES
	('MP4v/AAC over RTSP',
	'#transcode{vcodec=mp4v,vb=400,fps=25,scale=0.5,acodec=mp4a,ab=64,channels=2}:rtp{mp4a-latm,sdp=rtsp://0.0.0.0:5554/android.sdp}',
	'rtsp://{%SERVER_NAME%}:5554/android.sdp'
	);

-- 6
INSERT INTO plg_profiles (label, arg, link) VALUES
	('FLV/MP3 over HTTP(flv)',
	'#transcode{vcodec=FLV1,acodec=mp3,vb=200,deinterlace,fps=25,samplerate=44100,ab=32}:std{access=http{mime=video/x-flv},mux=ffmpeg{mux=flv},dst=0.0.0.0:8081/stream}',
	'http://{%SERVER_NAME%}:8081/stream'
	);

-- 7
INSERT INTO plg_profiles (label, arg, link) VALUES
	('H264/MP3 over HTTP(flv)',
	'#transcode{vcodec=h264,vb=200,deinterlace,ab=32,fps=25,width=256,height=192,acodec=mp3,samplerate=44100}:std{access=http{mime=video/x-flv},mux=ffmpeg{mux=flv},dst=0.0.0.0:8081/stream}',
	'http://{%SERVER_NAME%}:8081/stream'
	);

-- 8
INSERT INTO plg_profiles (label, arg, link) VALUES
	('H264/MP3 over RTSP',
	'#transcode{fps=15,audio-sync,vcodec=h264,venc=x264{vbv-maxrate=512,vbv-bufsize=512,preset=slow,profile=main,keyint=15},vfilter=canvas{width=800,height=480,padd},aenc=ffmpeg,acodec=mp3,ab=80,samplerate=44100,channels=2,soverlay}:rtp{mp4a-latm,sdp=rtsp://0.0.0.0:5554/android.sdp}',
	'rtsp://{%SERVER_NAME%}:5554/android.sdp'
	);
	

	
-- ONLINE LIBRARY CHANNELS --

INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fcp86825.live.edgefcs.net%2Flive%2Fcielo_std%4017630&live=1&quiet=1','direct-url','Italian Live Channels','Cielo TV','http://www.cielotv.it/','http://www.elisabistocchi.org/wp-content/uploads/2010/05/sky-cielo_tv-300x187.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fcp49989.live.edgefcs.net%3A1935%2Flive%3FvideoId%3D53404915001%26lineUpId%3D%26pubId%3D1445083406%26playerId%3D760707277001%26affiliateId%3D&live=1&playpath=streamRM1%402564&quiet=1','direct-url','Italian Live Channels','Sky TG 24','','http://www.tvstreaming.tv/wp-content/uploads/2009/11/sky_tg_24_logo.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fflash3.ipercast.net%2Fintertv.it%2Flive&live=1&quiet=1','direct-url','Italian Live Channels','Inter TV',':/','http://www.newsinter.it/wp-content/uploads/inter-tv.jpeg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2F109.123.96.196%3A1935%2Fws-anica%2Fcomingsoonlive&live=1&quiet=1','direct-url','Italian Live Channels','Cooming Soon TV','','http://www.informazione.it/pruploads/f7b8062b-debb-4a70-be32-0979ea674383/coming2.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2F77.92.78.90%2Fws-lexicon&live=1&playpath=%2Fws-lexicon%2Fsupertennistv&quiet=1','direct-url','Italian Live Channels','SuperTennis.tv','','http://www.improntalaquila.org/wp-content/uploads/2010/11/supertennis.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fflash.streamingmedia.it%2Fcanale9%2Flivestream&live=1&quiet=1','direct-url','Italian Live Channels','Canale 9','','http://cdn.cinetivu.com/wp-content/uploads/2010/01/Canale-9.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2F69-31-5-41.dv.livestream.com%2Fmogulus-stream-edge%2Fgrptelevisione%2Frtmp%3A%2F%2F69-31-5-70.dv.livestream.com%2FaffiliateStream%2Fgrptelevisione%2F6c69766572657065617465723a72746d703a2f2f36392d33312d352d37302e64762e6c69766573747265616d2e636f6d2f6d6f67756c75732f67727074656c65766973696f6e652f73747265616d475250&live=1&quiet=1','direct-url','Italian Live Channels','GRP','','http://www.mediakey.tv/typo3temp/pics/f8cb5c760c.jpg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fcp107861.live.edgefcs.net%2Flive%2FQVC_Italy_Stream1200%4034577&live=1&quiet=1','direct-url','Italian Live Channels','QVC','','http://www.tv14.net/wp-content/uploads/2010/10/QVC-Beauty.jpeg');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Flivestfslivefs.fplive.net%2Flivestfslive-live%2F&live=1&playpath=aljazeera_en_veryhigh%3FvideoId%3D747084146001%26lineUpId%3D%26pubId%3D665003303001%26playerId%3D751182905001%26affiliateId%3D&pageUrl=http%3A%2F%2Fenglish.aljazeera.net%2Fwatch_now%2F&app=aljazeeraflashlive-live%3FvideoId%3D747084146001%26lineUpId%3D%26pubId%3D665003303001%26playerId%3D751182905001&swfVfy=http%3A%2F%2Fadmin.brightcove.com%2Fviewer%2Fus1.24.04.08.2011-01-14072625%2FfederatedVideoUI%2FBrightcovePlayer.swf&quiet=1','direct-url','International Live Channels','Al Jazeera Eng','','');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Fstream2.france24.yacast.net%2Ffrance24_live%2Fen&live=1&playpath=f24_liveen&pageUrl=http%3A%2F%2Fwww.france24.com%2Fen%2Faef_player_popup%2Ffrance24_player&app=france24_live%2Fen&swfVfy=http%3A%2F%2Fwww.france24.com%2Fen%2Fsites%2Fall%2Fmodules%2Fmaison%2Faef_player%2Fflash%2Fplayer.swf&quiet=1','direct-url','International Live Channels','France 24 Eng','','');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2Ffms5.visionip.tv%2Flive&live=1&playpath=RT_3&pageUrl=http%3A%2F%2Frt.com%2Fon-air%2F&app=live&swfVfy=http%3A%2F%2Frt.com%2Fs%2Fswf%2Fplayer5.4.viral.swf&quiet=1','direct-url','International Live Channels','RT Eng','','');
INSERT INTO "videos" VALUES(NULL,'rtmpdump://stream/?rtmp=rtmp%3A%2F%2F95.211.73.3%2Flive%2F&live=1&playpath=22.sdp&quiet=1','direct-url','International Live Channels','ESPN','','');
INSERT INTO "videos" VALUES(NULL,'mms://live1.wm.skynews.servecast.net/skynews_wmlz_live300k','direct-url','International Live Channels','SkyNews','','');
INSERT INTO "videos" VALUES(NULL,'mms://verytangy-673-404284.wm.llnwd.net/verytangy_673-404284?e=1298596415&h=5289c5089ac9ce222b74349884966dc9&startTime=1298596405&userId=13194&portalId=5&portal=5&channelId=2627&ppvId=19848&mark=7817&source=box&epgType=live','direct-url','International Live Channels','BBC News','','');


-- AUTOOPTIONS

INSERT INTO plg_devices 
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
	('1', 'Asus Transformer', '/Android.*?Transformer [A-Z]{2}101/i', '0', '8', 'X_VlcShares_Plugins_WebkitRenderer', 'a:1:{s:12:"alt-profiles";a:4:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"6";i:3;s:1:"7";}}', '3');
INSERT INTO plg_devices
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
	('2', 'WiiMC', '/WiiMC/i', '0', '1', 'X_VlcShares_Plugins_WiimcPlxRenderer', 'a:0:{}', '4');
INSERT INTO plg_devices 
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
	('4', 'Generic Android', '/Android/i', '0', '5', 'X_VlcShares_Plugins_WebkitRenderer', 'a:1:{s:12:"alt-profiles";a:4:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"6";i:3;s:1:"7";}}', '2');
INSERT INTO plg_devices
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES
	('5', 'HTC Wildfire (Buzz)', '/Android.*?HTC Wildfire/i', '0', '5', 'X_VlcShares_Plugins_WebkitRenderer', 'a:1:{s:12:"alt-profiles";a:5:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"5";i:3;s:1:"6";i:4;s:1:"7";}}', '5');
INSERT INTO plg_devices
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES
	('6', 'Google Chrome Web Browser', '/Mozilla\/.*?AppleWebKit\/.*?(Chrome|Chromium)\/.*?Safari/i', '0', '7', 'X_VlcShares_Plugins_WebkitRenderer', 'a:1:{s:12:"alt-profiles";a:2:{i:0;s:1:"6";i:1;s:1:"7";}}', '6');
INSERT INTO plg_devices 
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
	('7', 'Generic Web Browser', '/Mozilla/i', '0', '6', 'X_VlcShares_Plugins_MobileRenderer', 'a:1:{s:12:"alt-profiles";a:1:{i:0;s:1:"7";}}', '0');
INSERT INTO plg_devices 
	("id", "label", "pattern", "exact", "idProfile", "guiClass", "extra", "priority") VALUES 
	('8', 'Webkit Browser', '/AppleWebKit/', '0', '6', 'X_VlcShares_Plugins_WebkitRenderer', 'a:1:{s:12:"alt-profiles";a:1:{i:0;s:1:"7";}}', '1');

	
	
	