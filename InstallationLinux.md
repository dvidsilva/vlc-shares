# Introduction #

This short tutorial give an example on how to install and do basic configuration for vlc-shares 0.5 on Linux

# Ubuntu 10.10 #

## Notes ##
In this tutorial will be used as placeholder the following strings:
  1. IP\_ADDRESS: is the lan ip address of the server where you are installing vlc-shares

This tutorial should works for Ubuntu 10.04 also. But there are some advices:
  1. VLC version in ubuntu 10.04 is old. It's better to install newer version.

## Details ##

  1. Open a console window
  1. Install the required packages
```
sudo apt-get install apache2 libapache2-mod-php5 php5 zend-framework zend-framework-bin vlc php5-sqlite php5-cli vlc
```
  1. **`[optional]`** Install FFMPEG package for FFMpeg helper:
```
sudo apt-get install ffmpeg
```
  1. **`[optional]`** Install extra codec for VLC:
```
sudo apt-get install libavcodec-extra-52 libavcodec-unstripped-52
```
  1. **`[optional]`** Install MediaInfo package for MediaInfo helper. You can download it from the official site: http://sourceforge.net/projects/mediainfo/files/binary/mediainfo-gui/0.7.35/mediainfo-gui_0.7.35-1_i386.Debian_5.deb/download
  1. Enable apache's mod\_rewrite:
```
sudo a2enmod rewrite
```
  1. Open Zend Framework config file:
```
sudo gedit /etc/php5/apache2/conf.d/zend-framework.ini
```
  1. Remove the _;_ from the begin of the second file (if it is there). The file should like below:
```
[Zend]
include_path=${include_path} ":/usr/share/php/libzend-framework-php"
```
  1. Save changes and exit
  1. Open Apache default site config:
```
sudo gedit /etc/apache2/sites-enabled/000-default
```
  1. Search for lines below:
```
<Directory /var/www/>
	Options Indexes FollowSymLinks MultiViews
	AllowOverride None
	Order allow,deny
	allow from all
</Directory>
```
> > and change it to:
```
<Directory /var/www/>
	Options Indexes FollowSymLinks MultiViews
	AllowOverride All
	Order allow,deny
	allow from all
</Directory>
```
  1. Unpack _vlc-shares 0.5_ package inside the _/var/www/_ directory.
  1. Change permissions and owner for vlc-shares directory:
```
sudo chown -R www-data:www-data /var/www/vlc-shares
sudo chmod a+rwx -R /var/www/vlc-shares/data
```
  1. Almost done. It's time to restart apache:
```
sudo /etc/init.d/apache2 restart
```
  1. Open your browser (Google Chrome or Firefox are supported) at http://localhost/vlc-shares/public. You should be redirected to the vlc-shares installer.
  1. Select your language
  1. Save changes


# You may find interesting... #

  * WiimcConfiguration: configure you WiiMC online media tab for VLCShares
  * AndroidDeviceConfiguration: configure VLCShares and Android Phones


# Fedora #

## Notes ##
Unfortunatly there is no tutorial for vlc-shares installation in Fedora, yet. But in vlc-shares's main thread in Wiimc forum you can find interesting tips for SELinux and vlc-shares configuration (in italian):

http://www.wiimc.org/forum/viewtopic.php?f=4&t=562&start=90#p3514