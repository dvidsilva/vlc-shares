Check recent [Issue Updates and Commit Activity using the Wiki Updates tab](Updates.md)

VLCShares is a web-app written in php. It aims to extend WiiMC online media tab features allowing to watch HD Files through vlc transcoding, fetch medias from websites (ex. Megavideo, AllSp, GoGoCinema.com, etc etc) and make them viewable in WiiMC without have to download them first. It also provide same features for Android Phones too.

| <a href='https://picasaweb.google.com/lh/photo/8rryG6x6HJHZ1bUrbqcgag?feat=embedwebsite'><img src='https://lh5.googleusercontent.com/_U6HIkh_ODAo/TWYYhAu6teI/AAAAAAAAAEc/UnDL5gSswLY/s288/12-dashboard.png' /></a> | <a href='https://picasaweb.google.com/lh/photo/1iaQQRdosiuRvcfyUE6Jdw?feat=embedwebsite'><img src='https://lh5.googleusercontent.com/_U6HIkh_ODAo/TWYYbiTEPTI/AAAAAAAAADw/kBtU92dBVE0/s288/01-installer.png' /></a> | <a href='https://picasaweb.google.com/lh/photo/YpmrhHRCJn-0kTwkFbnhKQ?feat=embedwebsite'><img src='https://lh6.googleusercontent.com/_U6HIkh_ODAo/TWYYgJNrJCI/AAAAAAAAAEU/HBGp4QYFCbQ/s175/10-collections-index.png' /></a> | <a href='https://picasaweb.google.com/lh/photo/R5dbXEnUF8YmIjaSlepghw?feat=embedwebsite'><img src='https://lh3.googleusercontent.com/_U6HIkh_ODAo/TWYYgXF7kJI/AAAAAAAAAEY/DQS25ZM_4_E/s175/11-animeftw.png' /></a> |
|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|

[More pictures...](https://picasaweb.google.com/ximarx/VLCShares053?feat=directlink)

### Do you like VLCShares? ###
Please, consider supporting VLCShares: [![](http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=GSV775S395QQU&lc=IT&item_name=VLCShares&item_number=vlc%2dshares&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted)

### The current stable version: 0.5.4 ###


Features list of version 0.5.4:

  * User Authentication
  * Watch HD Files in WiiMC and Android Phones through VLC-Transcoding
  * Browse and Watch your media stored in HDD
  * Use embedded or file subtitles while transcoding or alternative audio tracks
  * Watch videos hosted by Megavideo, Youtube, Veoh, Vimeo, Veetle, VideoBB, 4Shared, DailyMotion, MovShare, SopCast,...
  * Watch Live Channels and Sport events published on MyP2P through SopCast
  * Watch RTMP/RTMPE streams through RTMPDump
  * SopCast integration
  * Watch videos from NarutoGet.com
  * Watch videos from AnimeLand.it
  * Watch videos from DBForever.net
  * Watch videos from AllSp.com
  * Watch videos from OPFItalia.net
  * Watch videos from AnimeDb.tv
  * Watch videos from Jigoku.it
  * Watch videos from Youtube with **Closed Caption**
  * Watch videos from AnimeFTW.tv (...yes, all videos)
  * Watch videos from GoGoCinema.com
  * Watch videos from IceFilms.info
  * Watch some live channels from Rai.it
  * Check JDownloader status from WIIMC/Phone and append new links to the queue
  * Get infos about streams through WiiMC/Android Interface
  * New versions notifier
  * Automatic plugin installation
  * Translation file for Italian, English and Spanish languages
  * ....

![https://lh6.googleusercontent.com/-xvGQr1ZEo34/TyGCFyNhjDI/AAAAAAAAALg/PJANnlzselc/s800/hosters-updated.png](https://lh6.googleusercontent.com/-xvGQr1ZEo34/TyGCFyNhjDI/AAAAAAAAALg/PJANnlzselc/s800/hosters-updated.png)


Last version complete changelog:
```
*** 0.5.4 (22/4/2011) ***
 - Automatic installer for Ubuntu 10.10
 - Automatic installer for Windows: permissions fixed
 - Documentation updates in the wiki 
 - Authetication features
 - build script improved
 - robots.txt
 - System report
 - rules for magic_quotes and register_globals in htaccess and system tests
 - audio quality for Android LQ improved (ab=20 -> ab=64)
 - Android LQ is the new default profile for android devices
 - New hoster helper
 - New bookmarklets
 - Global Online library introduced with multi-hoster support (this one replace the Megavideo Library)
 - New set of plugins for many hosters (megavideo, megaupload, youtube, videobb, 4shared, veoh, veetle, dailymotion, sopcast)
 - Older plugins updated for new hoster api where needed
 - Pagination helper
 - RTMPDump integrated (without transcoding, so it doesn't work for android devices)
 - SopCast integrated
 - New plugin for MyP2P.eu
 - Online installation of plugins without have to download them first through plugin installer
 - A short (english only) plugin description is available in the installation page
 - Partial translation for es_ES language available
 - Some live TV channels (working through rtmpdump) added in Online Library (for testing)
 - VLCShares main url is changed from http://IPADDRESS/vlc-shares/public to http://IPADDRESS/vlc-shares/ 
```


**A list of additional plugins is available [in the wiki](PluginsList#Plugins_List.md)**

VLCShares is compatible with Windows XP Pro and later, OS Linux (a tutorial for installation in Ubuntu 10.10 is available in the wiki) and Mac OSX.


More infos: http://www.wiimc.org/forum/viewtopic.php?f=4&t=562


This video show how to use the new bookmarklets feature of Megavideo plugin

| <a href='http://www.youtube.com/watch?feature=player_embedded&v=GycWEv7cWLk' target='_blank'><img src='http://img.youtube.com/vi/GycWEv7cWLk/0.jpg' width='425' height=344 /></a> | <a href='http://www.youtube.com/watch?feature=player_embedded&v=P2SBEVw1Ku4' target='_blank'><img src='http://img.youtube.com/vi/P2SBEVw1Ku4/0.jpg' width='425' height=344 /></a> |
|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| &lt;wiki:gadget url="http://www.ohloh.net/p/485454/widgets/project\_basic\_stats.xml" height="220" border="1"/&gt; |