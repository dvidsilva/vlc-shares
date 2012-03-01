#################################################################
# __      ___       _____       _____ _                         #
# \ \    / / |     / ____|     / ____| |                        #
#  \ \  / /| |    | |   ______| (___ | |__   __ _ _ __ ___ ___  #
#   \ \/ / | |    | |  |______|\___ \| '_ \ / _` | '__/ _ | __| #
#    \  /  | |____| |____      ____) | | | | (_| | | |  __|__ \ #
#     \/   |______|\_____|    |_____/|_| |_|\__,_|_|  \___|___/ #
#     															#
########## WeebTv Plugin ########################################

WeebTv v0.1 beta3
Created by Francesco Capozzo (ximarx)
ximarx@gmail.com

All content of this file is released using
GPL v3 (http://www.gnu.org/licenses/gpl.html)

Allows to watch videos from weeb.tv

For more infos, browse the project site at http://code.google.com/p/vlc-shares/

This package include a modified version of rtmpdump + utils statically compiled for ubuntu
and windows (not tested). Patch applied to standard git checkout is available in the root for this package

/==========---
| Changelog
\==========---

*** 0.1 beta3 ***
 - Permissions for rtmp* executables automatically corrected in linux env (only vlc-shares 0.5.5alpha3+)
 - Using vlc to stream the content using stdout source from piped rtmpdump (no more rtmpgw)

*** 0.1 beta2 ***
 - Fix a bug in "not dev" environment

*** 0.1 beta ***
 - Initial version
