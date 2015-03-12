# Introduction #

This short tutorial give an example on how to install and do basic configuration for vlc-shares 0.5

# Automatic installation #

The version 0.5 comes with a EasyPHP 5.3.3 + VLCShares 0.5 installer. You can download it from the downloads section

After you completed the installation procedure, open your browser (chrome or firefox) to http://localhost/vlc-shares/public and complete the configuration (steps 14-15-16-17-18-19 of manual installation).


## Notes ##
If you use the installer, every time you will start easyphp you will get an error about "Mysql start problem". Ignore this error (i removed mysql from the package to reduce file dimension).

[Stefano Angaran ha scritto un articolo in italiano su VLCShares con alcune informazioni sull'installazione.](http://blog.upyou.it/2010/10/18/vlc-shares-e-wiimc-accoppiata-vincente-per-lo-streaming-con-la-wii-1033.html)

[Un tutorial in lingua italiana potete trovarlo a questo indirizzo grazie alla community di Wiitaly](http://wiitaly.altervista.org/forum/viewtopic.php?f=23&t=478&p=2021)

[How To Geek published a good tutorial about vlc-shares's installation. You can give a look to it if you are in troubles](http://www.howtogeek.com/howto/43484/how-to-get-airvideo-features-in-android-for-free)

# Manual Installation #

## Requirement ##

  * _vlc-shares 0.5_ package:
  * _Windows XP Pro_, _Windows Vista_ (all versions) o _Windows 7_ (all versions)
  * VLC 1.1.0+: http://sourceforge.net/projects/vlc/files/1.1.4/win32/vlc-1.1.4-win32.exe/download
  * _EasyPhp 5.3.x_: [https://sourceforge.net/projects/quickeasyphp/files/EasyPHP/5.3.3/EasyPHP-5.3.3-setup.exe/download](.md)
  * _Zend Framework 1.10.6+_ (minimal package): http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.zip
  * **`[`optional`]`** _Mediainfo for Windows_ (Cli version): http://sourceforge.net/projects/mediainfo/files/binary/mediainfo/0.7.35/MediaInfo_CLI_0.7.35_Windows_i386.zip/download for x86 or http://sourceforge.net/projects/mediainfo/files/binary/mediainfo/0.7.35/MediaInfo_CLI_0.7.35_Windows_x64.zip/download for x64
  * **`[`optional`]`** _FFMpeg for Windows_ (x32 static build version): http://ffmpeg.arrozcru.org/autobuilds/ffmpeg-latest-mingw32-static.7z

## Notes ##
In this tutorial will be used as placeholder the following strings:
  1. EASYPHP\_DIR: is the path to the EasyPhp install directory
  1. IP\_ADDRESS: is the lan ip address of the server where you are installing vlc-shares
  1. MEDIAINFO\_PATH: is the path to MediaInfo.exe (filename included). Ex: c:\mediainfo\MediaInfo.exe
  1. FFMPEG\_PATH: is the path to ffmpeg.exe (filename included). Ex: c:\ffmpeg\bin\ffmpeg.exe
  1. VLC\_PATH: is the path to vlc.exe (filename included). Ex: C:\vlc\vlc.exe

## Details ##

  1. Install _EasyPhp_
  1. Open the directory **EASYPHP\_DIR\www\**
  1. Unpack here the _vlc-shares 0.5_ package
  1. Unpack ONLY "/library/Zend/" dir (and sub dirs) inside Zend Framework 1.10.6 minimal package in **EASYPHP\_DIR\www\vlc-shares\library\**. (At least there will be EASYPHP\_DIR\www\vlc-shares\library\Zend\.......). [More info about how to unpack ZendFramework](http://code.google.com/p/vlc-shares/issues/detail?id=1)
  1. Install VLC
  1. Open the file **EASYPHP\_DIR\conf\_files\httpd.conf** with a text editor (Notepad or Wordpress should be fine, but Notepad++ is better)
  1. Change line 49 from `Listen 127.0.0.1:80` to `Listen 0.0.0.0:80`. (An alternative should be to use **IP\_ADDRESS** instead of 0.0.0.0)
  1. Change line 119 from `#LoadModule rewrite_module modules/mod_rewrite.so` to `LoadModule rewrite_module modules/mod_rewrite.so`  <-- (Remove the #)
  1. Change line 233 from `AllowOverride None` to `AllowOverride All`
  1. Save the changes and restart Apache server from the EasyPHP tray icon. [A sample config file for EasyPHP 5.3.3i can be downloaded from Downloads section](http://code.google.com/p/vlc-shares/downloads/detail?name=httpd.conf&can=2&q=)
  1. **WARNING**: some users report that EasyPHP sometimes doesn't recompile the apache config file: check the EasyPHP window (double click on EasyPHP tray icon to spawn it) for reports of a config file change. If there isn't any report, open manually the file **EASYPHP\_DIR\apache\conf\httpd.conf** and manually change it as for the previous file. (steps: 7-8-9-10)
  1. Right click on the EasyPHP tray icon, then select _configuration_->_PHP Extensions_
  1. Check on _php\_pdo\_sqlite_, then click on Apply and restart Apache server again
  1. **`[`optional`]`** Install/Unpack MediaInfo package
  1. **`[`optional`]`** Install/Unpack FFMpeg package
  1. Open your browser (Google Chrome or Firefox is better :P) at http://localhost/vlc-shares/public. You should be redirected to the new vlc-shares dashboard ![http://img827.imageshack.us/img827/4993/alpha5.png](http://img827.imageshack.us/img827/4993/alpha5.png)
  1. Click on **VLCShares - configure**
  1. Select your language, insert the VLC\_PATH, MEDIAINFO\_PATH (optional) and FFMPEG\_PATH (optional)
  1. Save changes

# You may find interesting... #

  * WiimcConfiguration: configure you WiiMC online media tab for VLCShares
  * AndroidDeviceConfiguration: configure VLCShares and Android Phones