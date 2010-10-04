#################################################################
# __      ___       _____       _____ _                         #
# \ \    / / |     / ____|     / ____| |                        #
#  \ \  / /| |    | |   ______| (___ | |__   __ _ _ __ ___ ___  #
#   \ \/ / | |    | |  |______|\___ \| '_ \ / _` | '__/ _ | __| #
#    \  /  | |____| |____      ____) | | | | (_| | | |  __|__ \ #
#     \/   |______|\_____|    |_____/|_| |_|\__,_|_|  \___|___/ #
#     															#
#################################################################

VLC-SHARES v0.5 alpha 1 - 20/09/2010
Created by Francesco Capozzo (Ximarx)
ximarx@gmail.com

All content of this file is released using
GPL v3 (http://www.gnu.org/licenses/gpl.html)

Vlc-share allow you to browse all files in your multimedia collection,
select one of them and start the stream (with transcoding) through vlc.
All of this through WiiMc (and the onlinemedia tab)

For more info go to (in italian) 
http://ximarx.netsons.org/blog/vlc-share/

Sommario:
PPP) Prerequisites
1) Install & Configure
2) WiiMc Configuration
3) Changelog
4) In the next release (maybe)
5) Known issue
6) Troubleshooting
7) Acknowledgements


/==========---
| PPP: Prerequisites
\==========---

VLC-Share has been tested with:
 - Ubuntu 10.04
 - Windows XP Pro SP3
 
Linux Ubuntu 10.04 requirements:
 - Apache 2.2 (official repository)
 - PHP 5.3+ (official repository)
 - VLC 1.0.6+ (official repository)
 - netcat (official repository)
 - Zend Framework 1.10.3 (official repository)
 - php5-sqlite (official repository)
 
Windows XP Pro SP3 requirementes:
 - EasyPhp 5.3.2i+ (http://sourceforge.net/projects/quickeasyphp/files/EasyPHP/5.3.2i/EasyPHP-5.3.2i-setup.exe/download)
 - Zend Framework 1.10.6 minimal (http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.zip)
 - VLC 1.1.0+ (http://sourceforge.net/projects/vlc/files/1.1.0/win32/vlc-1.1.0-win32.exe/download)
 - netcat for windows (comes with vlc-shares)
 - sysinternals PsExec (comes with vlc-shares)
 - taskkill & tasklist (provided w/ Windows XP Professional)

/==========---
| 1: Install & Configure
\==========---

###!!! THOSE INSTRUCTIONS ARE OLD

Installation & configuration tutorial:
http://ximarx.netsons.org/blog/vlc-share/installation-and-configuration-guide/

Config file is located in vlc-shares/application/configs/vlc-shares.config.ini
Select the config template for your system, rename it in vlc-shares.config.ini
and enjoy editing it :P

/==========---
| 2: Configure Wii (WiiMc)
\==========---

Add the line below in the file onlinemedia.xml in WiiMC directory

<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/public/" /> 
	
before the last line (</file>).
IP_ADDRESS = ip address of vlc-shares server

/==========---
| 3: Changelog
\==========---

*** 0.5 alpha 1 (20/09/2010) ***
 - plugin system has been rewritten
 - new stream option selection mode
 - new seek controls (you can input position or shift time)
 - megavideo plugin management has been improved (the plugin core is missing)
 - new bookmarklets features for megavideo plugin (the plugin core is missing)
 - configuration moved to db (located in /data/db/vlc-shares.db)
 - All work out of box. Only vlc path must be selected
 - Shares, Profiles and Output type has been moved in the db. An interface will be available in alpha 3
 - Vlc_Commander_Rc has been flagged as deprecated
 - Only in alpha release until 0.5 default config file is placed in application/configs/vlc-shares.newconfig.ini
 

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
| 4: In the next release (maybe)
\==========---
 
 - change audio stream (language) on the fly
 - change subtitle on the fly
 - seek to position
 - parental control
 - login support
 
/==========---
| 5: Known issues
\==========---

 - If one collection has shared only, WiiMc indicates "Error reading file"
 - trying to stream and transcoding from megavideo library can give audio only
 	or no-audio/no-video output. This is a vlc bug with h264/aac files and
 	transcode.
 	More info here: https://trac.videolan.org/vlc/ticket/2850
 - trying to play megavideo files directly give wiimc dumps/no video/no audio
 	output. Wiimc doesn't handle h264/aac files well.
  
/==========---
| 6: Troubleshooting
\==========---

 - i don't have Zend Framework in include_path and vlc-shares don't work:
 	copy Zend/ folder in Zend Framework 1.10.6-minimal zip file in "vlc-shares/library/" 
 - If one collection has shared only, WiiMc indicates "Error reading file":
 	it's not a vlc-share problem :(. Add another dummy collection.
 		
/==========---
| 7: Acknowledgements
\==========---
 
 - The people who created VLC
 - The people who created WiiMc
 - The people who created Zend Framework
 - The people who created Apache
 - The people who created PHP
 - The people who created PsExec (SysInternals)
 - The people who created NetCat
 - The people who created NetCat for Windows
 - luruke, who created Megavideo Downloader class
 	 (http://forum.codecall.net/classes-code-snippets/14324-php-megavideo-downloader.html)
 - The people who created $ringraziamento_value
 
 