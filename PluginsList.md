# Plugin Installer #

The PluginInstaller is a special plugin that allows the installation of external plugins inside VLCShares

It's installed by default from vlc-shares 0.5.1beta1 (but for some bugs, plugin eggs for version 0.5.1beta1 aren't compatible with PluginInstaller version that comes with vlc-shares 0.5.1beta2 or later.

The PluginInstaller interface is available clicking on the icon in the vlc-shares's dashboard:

![http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_dashboard.png](http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_dashboard.png)

## How to install new plugins ##

There is nothing more easy than this: you have only to submit the plugin xegg file from the PluginInstaller interface in the right side of the page:

![http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_panel_install.png](http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_panel_install.png)

You will find all previously additional installed plugins in the list on the right side of the PluginInstaller management interface in the left side of the screen:

![http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_panel_list.png](http://vlc-shares.googlecode.com/svn/wiki/images/plugininstaller_panel_list.png)

A list of additional plugins is available in the [Plugins List](PluginsList#Plugins_List.md) section, below.

# Plugins List #

## Data Providers / Channels ##

This kind of plugins adds new collections inside VLCShares

| **Name** | **Key** | **Last Version** | **Compatibility/Requirement** | **Description** |
|:---------|:--------|:-----------------|:------------------------------|:----------------|
| **[Anime Db](PluginsList#Anime_Db.md)** | animedb | 0.1.1 | 0.5.1 or later & megavideo plugin | Adds the site animedb.tv inside VLCShares|
| **[Youtube](PluginYoutube.md)** | youtube | 0.1 | 0.5.1 and 0.5.2 | Adds Youtube inside VLCShares_, with support for closed caption_|
| **[Youtube](PluginYoutube.md)** | youtube | 0.1.2 | 0.5.3 or later | Adds Youtube inside VLCShares_, with support for closed caption_|
| **[OPFItalia](PluginsList#OPFItalia.md)** | opfitalia | 0.2.1 | 0.5.1 or later  & megavideo plugin| Adds the site opfitalia.net inside VLCShares|
| **Jigoku** | jigoku | 0.1.2 | 0.5.1 or later & megavideo plugin | Adds the site http://www.jigoku.it inside VLCShares|
| **AnimeFTW** | animeftw | 0.2.2 | 0.5.1 or later | Adds the site http://www.animeftw.tv inside VLCShares|
| **Megavideo** | megavideo | 0.2.1 | 0.5.3 or later | Adds megavideo with premium account support inside VLCShares|
| **AnimeLand** | animeland | 0.2 | 0.5.3 or later | Adds animeland.it inside VLCShares|
| **NarutoGet** | narutoget | 0.2 | 0.5.3 or later | Adds narutoget.com inside VLCShares|
| **DBForever** | dbforever | 0.2 | 0.5.3 or later | Adds dbforever.net inside VLCShares|
| **AllSp (southpark)** | allsp | 0.2 | 0.5.3 or later | Adds allsp.com inside VLCShares|
| **DirettaRAI** | direttarai | 0.1 | 0.5.3 or later (italian users only) | Adds some RAI live channels inside VLCShares|
| **GoGoCinema** | gogocinema | 0.1 | 0.5.3 or later | Adds gogocinema.com inside VLCShares|
| **IceFilms** | icefilms | 0.1 | 0.5.3 or later & megavideo plugin 0.2.1 or later| Adds icefilms.info inside VLCShares|
| **FilmStream** | filmstream | 0.1 | 0.5.3 or later & megavideo plugin 0.2.1 or later| Adds filmstream.tv inside VLCShares|

**3rd party**

| **Name** | **Key** | **Last Version** | **Compatibility/Requirement** | **Description** |
|:---------|:--------|:-----------------|:------------------------------|:----------------|
| **SpainTv** | spaintv | 0.2 | 0.5.3 or later | Adds some spanish tv. Done by Miguel Angel Pescador Santirso (mpescadorsantirso@gmail.com) |
| **SpainRadio** | spainradio | 0.2 | 0.5.3 or later | Adds some spanish tv. Done by Miguel Angel Pescador Santirso (mpescadorsantirso@gmail.com) |


## Management features ##

This kind of plugins adds new features inside VLCShares

| **Name** | **Key** | **Last Version** | **Compatibility/Requirement** | **Description** |
|:---------|:--------|:-----------------|:------------------------------|:----------------|
| **Cache** | cache | 0.1 | 0.5.1 and 0.5.2 | Backported version of the cache manager bundle in versione 0.5.3 for older versions |
| **JDownloader** | jdownloader | 0.1 | 0.5.3 | Allow to check JDownloader status and append new Megavideo link to the download queue |
| **FsThumbs** | fsthumbs | 0.1 | 0.5.3 & ffmpeg helper enabled | Show thumbnails for videos in shared folders |
| **AudioSwitcher** | audioSwitcher | 0.1 | 0.5.1->3 & ffmpeg helper enabled | Allow to change audio track while transconding |


# Plugins Guide #

## WiimcPlxRenderer ##

## MobileRenderer ##

## Profiles ##

## Outputs ##

## Anime Db ##

The `AnimeDb` plugin allow to watch all videos of the _AnimeDb.tv_ site through VLCShares. This plugin is compatible with versions 0.5.1beta2 and later.
A Xegg archive of `AnimeDb` in stored inside the download section of this site and can be installed through the PluginInstaller interface. [Follow this tutorial](PluginsList#How_to_install_new_plugins.md) to install the component.


The management interface is available clicking on the `AnimeDb` dashboard icon

![http://vlc-shares.googlecode.com/svn/wiki/images/animedb_dashboard.png](http://vlc-shares.googlecode.com/svn/wiki/images/animedb_dashboard.png)

To be sure to watch all videos, you have to insert a valid AnimeDb.tv username/password in  plugin configuration panel. AnimeDb.tv accounts can be registered freely in the site.

![http://vlc-shares.googlecode.com/svn/wiki/images/animedb_configs.png](http://vlc-shares.googlecode.com/svn/wiki/images/animedb_configs.png)

After you have completed the installation and configuration procedure, AnimeDb.tv videos will be available in the VLCShares's Collections Index

![http://vlc-shares.googlecode.com/svn/wiki/images/animedb_collections.png](http://vlc-shares.googlecode.com/svn/wiki/images/animedb_collections.png)

## OPFItalia ##

The `OPFItalia` plugin allow to watch all videos of the _opfitalia.net_ site through VLCShares. This plugin is compatible with versions 0.5.1 and later.
A Xegg archive of `OPFItalia` in stored inside the download section of this site and can be installed through the PluginInstaller interface. [Follow this tutorial](PluginsList#How_to_install_new_plugins.md) to install the component.


The management interface is available clicking on the `OPFItalia` dashboard icon

![http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_dashboard.png](http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_dashboard.png)

To be sure to watch all videos, you have to insert valid OPFItalia.net/streaming/ username/password in  plugin configuration panel. OPFItalia.net accounts can be registered freely in the site.

![http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_configs.png](http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_configs.png)

After you have completed the installation and configuration procedure, OPFItalia.net videos will be available in the VLCShares's Collections Index

![http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_collections.png](http://vlc-shares.googlecode.com/svn/wiki/images/opfitalia_collections.png)