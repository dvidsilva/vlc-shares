#################################################################
# __      ___       _____       _____ _                         #
# \ \    / / |     / ____|     / ____| |                        #
#  \ \  / /| |    | |   ______| (___ | |__   __ _ _ __ ___ ___  #
#   \ \/ / | |    | |  |______|\___ \| '_ \ / _` | '__/ _ | __| #
#    \  /  | |____| |____      ____) | | | | (_| | | |  __|__ \ #
#     \/   |______|\_____|    |_____/|_| |_|\__,_|_|  \___|___/ #
#     															#
#################################################################

VLC-SHARES v0.5.5 beta3 - 23/06/2012
Created by Francesco Capozzo (Ximarx)
ximarx@gmail.com

All content of this file is released using
GPL v3 (http://www.gnu.org/licenses/gpl.html)

Vlc-shares allow you to browse all files in your multimedia collection,
select one of them and start the stream (with transcoding) through vlc.
All of this through WiiMc (and the onlinemedia tab)

For more infos, browse the project site at http://code.google.com/p/vlc-shares/

/==========---
| Changelog
\==========---

*** 0.5.5 beta3 (23/06/2012) ***
 - Fix for vlc 2.0
 - Rtmpdump bootstrap changed (need test)

*** 0.5.5 beta2 (10/03/2012) ***
 - Automatic category selection when adding a new link from inside a category
 - New bookmarklets:
    * allow to capture manually all kind of links
    * allow to bookmark page
    * crazy-page proof
 - New plugin bookmarks:
    * allow to bookmarks page and get them parsed on the fly for new video links from vlc-shares.
    * Bookmarks added through the new bookmarklet can store authentication cookies for page that requires user login.
     (Google Chrome extension "cookie.txt export MOD" can be used to catch HttpOnly cookies. Downloadable from project site)
 - New Threads Manager GUI:
    * allow to get threads informations and to stop/clear/start them if there are pending jobs
 - Fixed few bugs to transcoding mode startup
 - Fixed a bug in SopCast plugin displaying "back to stream" link even if sopcast is not the selected streamer engine

*** 0.5.5 beta (05/03/2012) ***
 - Auth plugin is always enabled now. Login can be enabled during installation or from configs page (as configuration)
 - ACL features added
 - Basic rules for core resources
 - ACL helper added: allow basic acl management
 - New ACL fragment in plugin manifest. More info in docs/manifest_plugin.xml
 - New file attributes in files section in plugin manifest:
 	* file can be replaced if explicitally required
 	* file permissions can be specificed (for linux env)
 	* failed file copy can be ignored if specified
 	* ... more info in docs/manifest_plugin.xml
 - vlc-shares configuration page merged with plugins configuration
 - new api for form element management
 - core plugins management features moved inside plugin installer controller
 - new repository plugin installation gui (thumbs support)
 - new optional plugin installation gui during installation (thumbs support)
 - guis for acl/permissions management
 - security implemented in HttpPost thread starter 
 - getManageIndexLinks for lazy plugins deprecated
 - Webkit renderer is not default gui anymore for webkit devices because it requires lots of bug fixes. It can still manually selected
 - Vlc 2.0 supported
 - Default vlc version setted to 2.0
 - Vlc log can be enabled in configurations
 

*** 0.5.5 alpha 2 (21/02/2012) ***
 - Outputs and Profiles plugins merged
 - New Devices plugin + new devices compatibility system
 - Automatic hoster selection in Online Library
 - New Webkit interface for Webkit devices
 - New PageParserLib
 - Helper broker allow core helper substitution
 - Compatibility with VLC 1.2 http server
 - New Plugins_Utils utility class
 - New RendererInterface for Renderer plugins
 - DEB package for Ubuntu 11.10
 - Scripts for DEB and ISS installer creation (ISS creation through cross compiling thanks to wine)
 - Transcoding profiles improvements: better quality, mime-type for streams
 - Transcoding profiles merged with outputs profiles
 - Mediainfo helper removed
 - Stream helper redirected to ffmpeg
 - Automatic stream type recognition before transcoding removed
 - Profile for Asus Transformer
 - Move streamers engine to multithreads

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

*** 0.5.3 (23/02/2011) ***
 - Some fixes to object interfaces of X_Vlc_... objects
 - [EXTRA][AnimeDB] New link scaper algorithm submitted by Valerio Moretti can be selected in config page (it's experimental)
 - [UpdateNotifier] New plugin for update notification (for vlc-shares and official plugins)
 - [EXTRA][AnimeLand, AllSp, DBForever, NarutoGet] Moved to extra
 - Better error handling for WiimcPlxRenderer: now we can get error information in WiiMC
 - Installation script allow to automatically download and install optional plugins
 - [EXTRA][Megavideo] Removed urlencoding of username/password while authentication agains megavideo site
 - [Backupper] Backup file changed with value encryption
 - [EXTRA][DirettaRai] New plugin added for live channel from RAI (italian television)
 - Cache plugin integrated
 - Lazy plugins configuration redirect on configs change fixed
 - [MobileRenderer] Fixed a bug in mode/selection page that provoke multiple calls to the same page
 - [FileSystem] Disable cache when it's the provider
 - [PluginInstaller] VERSION_TO param in plugin manifest is handled as an upper bound and it's the first incompatible version
 - [PluginInstaller] Fixed regression in plugin removal procedure 
 - Translation function allow params substitution
 - [EXTRA][JDownloader] JDownloader plugin added
 - [EXTRA][Megavideo] Fixed regression in category removal/rename
 - [EXTRA][Megavideo] Missing translation strings added
 - [EXTRA][Youtube,Megavideo] Cache disabled when playlist is built with local data only
 - [EXTRA][AnimeFTW, AnimeDB, Megavideo] Form password element type changed to password
 - [EXTRA][AnimeFTW, AnimeDB, Megavideo] Cookie jar reset on plugin configs change
 - New management gui
 - Improved error reporting
 - [Issue-8] Fixed problems while sharing root drives folders in windows env
 - [EXTRA] Youtube: updated to support restricted content (with 240p quality only)
 - [EXTRA] AnimeFTW: fixed auth problems with video in server1 forcing proxy method to all videos
 - [EXTRA] AnimeFTW: removed hide user-agent config and user-agent locked to vlc-shares/VERSION animeftw/VERSION
 - [EXTRA] Jigoku: stable release
 - [EXTRA] Megavideo: moved to extra
 - [EXTRA] Megavideo: added premium account support
 - [EXTRA] Megavideo: support for megaupload->megavideo videos (?d= links)

*** 0.5.2 (16/12/2010) ***
 - Support for WiiMC 1.1.1 and WiiMC 1.1.1+
 - Fix a regression in configs label and description
 - Added more infos in debug log

*** 0.5.1 (01/12/2010) ***
 - new MobileRenderer plugin for better navigation with android devices
 - new PluginInstaller (required to install standalone plugins)
 - new Backup/Restore system for configurations
 - tweaks
 - better transcoding profiles for android devices
 - bugfixes 
 - changes inside plugin api (input and output params)
 - Added and improved support for WiiMC+
 - Critical fixes
 - PclZip library updated to last version
 - Code tweaks
 - COM::run replace PsExec in Windows
 - [EXTRA] Plugin for AnimeDB.tv 
 - [EXTRA] Plugin for YouTube 
 - [EXTRA] Plugin for OPFItalia.net (old version doesn't work)

*** 0.5 (07/10/2010) ***
 - plugin system has been rewritten
 - new stream options selection mode
 - new seek controls (you can input position or shift time)
 - megavideo plugin management has been improved
 - new bookmarklets features for megavideo plugin
 - configuration moved to db (located in /data/db/vlc-shares.db)
 - All work out of box. Only vlc path must be selected
 - Vlc_Commander_Rc has been flagged as deprecated and nc.exe removed from the package
 - Removed old deprecated plugins
 - Added layout support
 - Added new manage interface
 - Stabilized lvl 2 api:
   * new api for news
   * new api for alert
   * new api for stats
   * new api for quick actions
   * new api for plugin management
 - Blueprint css framework included in dev tree
 - Jquery/Jquery ui/lightbox included in dev tree
 - Added 2 soc plugins for expose how to use new lvl 2 apis (CoreStats and WidgetDevAlert)
 - Configs interface has been added
 - Plugins management is available through interface
 - Bugfix to megavideo plugin
 - FileSystem plugin has a management interface
 - mediainfo and ffmpeg helper for stream analysis (embedded subs. auto profiles selection) implemented
 - Added configs for helpers
 - New plugin for videos in DBForever.net (in Italian)
 - New plugin for videos in AnimeLand.it (in Italian)
 - New plugin for south park episodies in AllSP.com (in English)
 - New manage interface for Profiles plugin
 - Added new plugin for NarutoGet.com
 - Installation script added
 - Added plugin for site opfitalia.net
 - Added installer script for windows 
 
 
*** 0.4.1 (20/7/2010) ***
 - plugin for Megavideo Library has been added
 - plugin for PLX->HTML conversion while browse collections with browser has been added
 - plugin for android phones has been added (tested with Motorola Milestone/Droid)
 - new home page has been added (http://IP_ADDRESS/vlc-shares/public)

*** 0.4 (15/7/2010) ***
 - new plugin system added
 - almost 100% of code has been rewritten
 - added 2 new type of vlc command interface: Commander_HTTP (works with vlc's -I http) and Commander_RC (works with vlc's -I oldrc)
 - windows version is much more faster (using Commander_HTTP)
 - plugin to hide hidden files while browsing has been added
 - completely customizable through plugin section in config file


*** 0.3.2 (8/7/2010) ***
 - it works with apache on port != 80 (for real now :P)
 - added test page: check it at http://YOUR_IP_ADDRESS/vlc-shares/public/test
 - added debug_log configuration


*** 0.3.1 (8/7/2010) ***
 - it works with apache on port != 80


*** 0.3 (7/7/2010) ***
 - it works on windows (with EasyPhp)
 - add links to go back to collection's index
 - configuration file has been rewritten
 

*** 0.2 (7/7/2010) ***
 - now stream can be paused/resumed
 - now you can go back and forth in the stream
 - displays the total/current time of reproduction


*** Versione 0.1 (6/7/2010) ***
 - first release
 
 
/==========---
| Troubleshooting
\==========---

 - i don't have Zend Framework in include_path and vlc-shares don't work:
 	copy Zend/ folder in Zend Framework 1.10.6-minimal zip file in "vlc-shares/library/" 
 		
