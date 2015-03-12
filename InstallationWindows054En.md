# Introduction #

This guide help you to installa VLCShares 0.5.4 on Windows (XP Pro, Vista, 7)



# Method 1: automatic installation #

## Notes ##

The VLCShares installer for Windows is based on the EasyPHP 5.3.3i installer with few changes inside Apache and Php configuration. If you want more information about the changes, read the `Method 2: manual installation` paragraph.


## Procedure ##

Start downloading the [VLCShares 0.5.4 installer for Windows](http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_installer.exe).

On download completed, execute the installer and follow on-screen instructions. While installing, Admin privileges will be required if UAC is enabled on Windows Vista or Windows 7.

After the installation is completed, Windows Firewall will ask for security settings about the Apache service (like in the image below)

![http://www.dsl.uow.edu.au/~sk33/pub/php5-2.png](http://www.dsl.uow.edu.au/~sk33/pub/php5-2.png)

Click on "Unlock".

Go on to [Third party software installation](#Third_party_software_installation.md)

# Method 2: manual installation #

## Notes ##

This guide is written for EasyPHP 5.3.3i (but should works for later versions too) as base for VLCShares.

## Requirements installation ##

Download an EasyPHP installer from the official site: http://www.easyphp.org/download.php. **(The best choise is the version 5.3.3i because this guide is built for it, but a later version should work as good as the 5.3.3i)**

Execute the installer and complete the installation following on-screen instructions.

## Apache and PHP configurations ##

Do a double click on the EasyPHP SysTray icon (the icon in the bottom-right side of the screen, in the Windows tray bar) to make the EasyPHP main windows visible.

![http://technology.ohmygoh.com/wp-content/uploads/2009/08/easyphp_changeport.jpg](http://technology.ohmygoh.com/wp-content/uploads/2009/08/easyphp_changeport.jpg)

Click on `E`->`Configuration`->`Apache` just like in the picture.

A text editor will be opened (Notepad by default) with the content of `Httpd.conf` inside it, the Apache configuration file.

Search for the line below:

```
Listen 127.0.0.1:80
```

and change it to:

```
Listen 0.0.0.0:80
```

Then, search for this line:

```
#LoadModule rewrite_module modules/mod_rewrite.so
```

and remove the `#`:

```
LoadModule rewrite_module modules/mod_rewrite.so
```

Now, search for

```
<IfModule alias_module>
    #
    # Redirect: Allows you to tell clients about documents that used to 
    # exist in your server's namespace, but do not anymore. The client 
    # will make a new request for the document at its new location.
    # Example:
    # Redirect permanent /foo http://localhost/bar
```

and append those line just under it:

```

	# VLCSHARES MODULE
	Alias /vlc-shares "${path}/vlc-shares/public"
	<Directory "${path}/vlc-shares">
		Options FollowSymLinks Indexes
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>
```

The final result should be like the one below:

```

<IfModule alias_module>
    #
    # Redirect: Allows you to tell clients about documents that used to 
    # exist in your server's namespace, but do not anymore. The client 
    # will make a new request for the document at its new location.
    # Example:
    # Redirect permanent /foo http://localhost/bar

	# VLCSHARES MODULE
	Alias /vlc-shares "${path}/vlc-shares/public"
	<Directory "${path}/vlc-shares">
		Options FollowSymLinks Indexes
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>

```

Save the changes and close the text editor. If EasyPHP ask for a configuration file update, click Yes.

If you are in trouble changing the httpd.conf file, you can download a pre-modified version of the file for EasyPHP 5.3.3i and replace your file with it. Download the file http://vlc-shares.googlecode.com/files/httpd_0.5.4.conf, rename it to `httpd.conf` and overwrite the file in `EASYPHP\conf_files\httpd.conf`.


Now it's the time for PHP configuration.

From the main EasyPHP window, click on `E`->`Configuration`->`PHP Extension`.

http://2.bp.blogspot.com/_jGEuU1zFjq8/R_ZZdbfEJhI/AAAAAAAAAKE/pbZeAbu8_Xg/s320/EasyPHP+curl.JPG

Add the `V` near the following modules:

  * `php_pdo_sqlite`
  * `php_openssl`
  * `php_sqlite`
  * `php_sqlite3`
  * `php_curl`
  * `php_mcrypt` (se presente)

After that, click on Apply and then Close

## File installation ##

Download the VLCShares manual installation archive from [this link](http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4.zip).

Unpack the content of it in the `EASYPHP` installation directory (the `vlc-shares/` folder will be created automatically).

## Zend Framework installation ##

Download Zend Framework (version 1.10.6 or later): you can follow [this link for the 1.10.6 version](http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.zip) or you can choose your version from [the download index](http://framework.zend.com/download/latest) (a `Minimal Package` version is enough).

Unpack the content of the Zend Framework archive in the Desktop folder.

Then move the `library/Zend` into `EASYPHP\vlc-shares\library\`.

After all changes, the EASYPHP + VLCShares folders structure could look like this:

```
EASYPHP/
   |--- apache/
   |--- conf_files/
   |--- ...
   |--- vlc-shares/
   |        |--- application/
   |        |--- library/
   |        |       |--- Zend/
   |        |       |      |--- Acl/
   |        |       |      |--- ...
   |        |       |--- X
   |        |       |--- ...
   |        |--- ...
   |--- ...
```


# Third party software installation #

VLCShares uses 3rd party software to provide some features. In this step we will install those software enabling VLCShares features. More specifically:
  * **VLC**: allow to enable transcoded video mode (required for Android Phones)
  * **FFMPEG**: used by VLCShares to fetch video stream information (video/audio formats, number and type of included tracks, ...) and to create thumbnail images of videos. Features like "Embedded subtitles", "Multiple audio switching" or "Automatic transcoding profile selection" are available only when FFMPEG is installed.
  * **SOPCAST**: used for SOP streams.
  * **RTMPDUMP**: used for RTMP/RTMPE streams, usually live channels.

## VLC installation ##

Download the installer of the last VLC version from [the official website](http://www.videolan.org/vlc/) and follow on-screen instructions.

## FFMpeg installation ##

Download FFMPEG for Windows (static build version) from http://ffmpeg.arrozcru.org/autobuilds/ffmpeg-latest-mingw32-static.7z.

Unpack it somewhere (`C:\Program Files\FFMpeg\` for example).

## Sop Cast installation (optional) ##

Download the installer of the last available version of Sop Cast [from the official website](http://www.sopcast.org/download). Execute it following on-screen instructions.

## RTMPDump installation (optional) ##

Download the last version of RTMPDump for Windows available from [the official website](http://rtmpdump.mplayerhq.hu/download).

You can download the version 2.3 (the most updated now) from [this link](http://rtmpdump.mplayerhq.hu/download/rtmpdump-2.3-windows.zip).

**Note**: some days ago Adobe introduced a new handshake protocol (Type 9) that is not supported by RTMPDump yet. For this reason some stream links should not work (all requiring the new Type 9 Handshake, Hulu for example) or work only with the RTMPDump version 2.1d or older (this is caused by a bug in RTMPDump). If you are in trouble with some RTMPDump streams, try to use the old 2.1d version. If even this doesn't work, you have to wait for RTMPDump updated about the new Handshake.

Unpack the content somewhere (for example `C:\Program Files\RTMPDump\`).


# And now? #

After you have completed all previous steps, move on [post installation guide](PostInstallConfiguration054En.md).