#################################################################
# __      ___       _____       _____ _                         #
# \ \    / / |     / ____|     / ____| |                        #
#  \ \  / /| |    | |   ______| (___ | |__   __ _ _ __ ___ ___  #
#   \ \/ / | |    | |  |______|\___ \| '_ \ / _` | '__/ _ | __| #
#    \  /  | |____| |____      ____) | | | | (_| | | |  __|__ \ #
#     \/   |______|\_____|    |_____/|_| |_|\__,_|_|  \___|___/ #
#     															#
########## RealDebrid Plugin ####################################

RealDebrid v0.1.5
Created by Francesco Capozzo (ximarx)
ximarx@gmail.com

All content of this file is released using
GPL v3 (http://www.gnu.org/licenses/gpl.html)

Allow to use premium account of real debrid for many hosters and add basic support (no info autofetching)
for (even without hoster plugin installed):
 - 4Shares
 - Megaporn
 - Megaupload
 - Megavideo
 - RapidShare
 - VideoBB
 - Hulu
 - Videozer
 - Videoweed
 - CBS
 - CWTv
 - bitshare.com
 - filefactory.com
 - hotfile.com
 - justin.tv
 - load.to
 - mediafire.com
 - megashares.com
 - netload.in
 - novamov.com
 - putlocker.com
 - uploaded.to (u.to)
 - wat.tv
 - wupload.com
 - abc.go.com
 - sockshare.com

For more infos, browse the project site at http://code.google.com/p/vlc-shares/

/==========---
| Changelog
\==========---

*** 0.1.5 ***
 - sockshare.com
 - uploaded.to pattern improved
 - http/https accepted both in regex for some hosters

*** 0.1.4 ***
 - new hosters:
 	abc.go.com
 - workaround for wrong link generation for videoweed on removed files

*** 0.1.3 ***
 - new hosters:
 	bitshare.com
	filefactory.com
	hotfile.com
	justin.tv
	load.to
	mediafire.com
	megashares.com
	netload.in
	novamov.com
	putlocker.com
	uploaded.to (u.to)
	wat.tv
	wupload.com
 - fixed a bug in videozer and videoweed hoster resolver

*** 0.1.2 ***
 - updated to new site layout
 - new hosters added: VIDEOZER, HULU, CWTV.com, CBS.com, Videoweed and 2Shared
 - auto selected stream is always the last one (if more than one is available), usually SD quality
 - improved authentication cache system


*** 0.1.1 ***
 - Megaupload hoster allows links of type megaupload.com/?d=

*** 0.1 ***
 - initial version
