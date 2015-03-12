# Introduction #

This tutorial show how to configure WiiMC for VLCShares



# Notes #

In this tutorial will be used as placeholder the following strings:
  1. IP\_ADDRESS: is the lan ip address of the server where you installed vlc-shares

# Version 0.5.4 #

## ...without User Authentication ##

Open the file **`onlinemedia.xml`** in Wiimc application folder. Il should look like this:
```
<?xml version="1.0" encoding="utf-8"?>
<file app="WiiMC" version="1.1.7">
<link name="Navi-Xtreme Media Portal" addr="http://navix.turner3d.net/wiilist/" />
<link name="SHOUTcast Radio" addr="http://www.wiimc.org/media/shoutcast_radio.php" />
<link name="SHOUTcast TV" addr="http://www.wiimc.org/media/shoutcast_tv.php" />
<link name="YouTube" addr="http://www.wiimc.org/media/youtube.php" />
<link name="YouTube - Search" type="search" addr="http://www.wiimc.org/media/youtube.php?q=" />

<folder name="Radio">
<link name="Radio Plus" addr="http://radioplus.dnsalias.org:8000/listen.pls" />
<link name="Otvoreni radio" addr="http://82.193.201.234:8001/listen.pls" />
<link name="Woxy radio" addr="http://woxy.lala.com/stream/aac32.pls" />
</folder>

</file>
```

Add the line below before `</file>`:

```
<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/" />
```

The complete file after the change should look like this one:
```
<?xml version="1.0" encoding="utf-8"?>
<file app="WiiMC" version="1.1.7">
<link name="Navi-Xtreme Media Portal" addr="http://navix.turner3d.net/wiilist/" />
<link name="SHOUTcast Radio" addr="http://www.wiimc.org/media/shoutcast_radio.php" />
<link name="SHOUTcast TV" addr="http://www.wiimc.org/media/shoutcast_tv.php" />
<link name="YouTube" addr="http://www.wiimc.org/media/youtube.php" />
<link name="YouTube - Search" type="search" addr="http://www.wiimc.org/media/youtube.php?q=" />

<folder name="Radio">
<link name="Radio Plus" addr="http://radioplus.dnsalias.org:8000/listen.pls" />
<link name="Otvoreni radio" addr="http://82.193.201.234:8001/listen.pls" />
<link name="Woxy radio" addr="http://woxy.lala.com/stream/aac32.pls" />
</folder>

<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/" />
</file>
```


## ...with User Authentication ##


Open your browser and go to the VLCShares Dashboard (**http://localhost/vlc-shares/** usually).

Click on the `User Account` button in the top bar.

[![](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta8B6cEmE_I/AAAAAAAAAIo/t9GJzOHyg_A/s400/user_accounts.png)](https://picasaweb.google.com/lh/photo/a8eppb3amyNJ4e0weOeIRpQcgAM_hV8aasQj0QleA50?feat=directlink)

Select the `Direct Login URL` for your Username and copy it.

Open the file **`onlinemedia.xml`** in Wiimc application folder and append the  a line like the one below before the `</file>` tag:

```
<link name="VLC-Shares Collections" addr="PASTE_HERE_YOUR_DIRECT_LOGIN_URL" />
```

Remeber to replace `%IP_ADDRESS%` inside the `Direct Login URL` with your VLCShares server IP Address.

The result should be like the one below:


```
<?xml version="1.0" encoding="utf-8"?>
<file app="WiiMC" version="1.1.7">
<link name="Navi-Xtreme Media Portal" addr="http://navix.turner3d.net/wiilist/" />
<link name="SHOUTcast Radio" addr="http://www.wiimc.org/media/shoutcast_radio.php" />
<link name="SHOUTcast TV" addr="http://www.wiimc.org/media/shoutcast_tv.php" />
<link name="YouTube" addr="http://www.wiimc.org/media/youtube.php" />
<link name="YouTube - Search" type="search" addr="http://www.wiimc.org/media/youtube.php?q=" />

<folder name="Radio">
<link name="Radio Plus" addr="http://radioplus.dnsalias.org:8000/listen.pls" />
<link name="Otvoreni radio" addr="http://82.193.201.234:8001/listen.pls" />
<link name="Woxy radio" addr="http://woxy.lala.com/stream/aac32.pls" />
</folder>

<link name="VLC-Shares Collections" addr="http://192.168.1.1/vlc-shares/auth/login/m/alt/u/admin/p/8038b9a7fdc2587fc275cf81e23bf3d2" />
</file>
```


# Version 0.5 #

  1. Open you browser at vlc-shares dashboard (http://localhost/vlc-shares/public/)
  1. Check in the plugin management section if the plugin _WiiMC Support_ is enabled
    1. If the plugin isn't enabled click on VLCShares - configure
    1. Search in the right column the plugin _WiimcPlxRenderer_ and enabled it
  1. Open the file **onlinemedia.xml** in Wiimc application folder. Il should look like this:
```
<?xml version="1.0" encoding="utf-8"?>
<file app="WiiMC" version="1.0.7">
<link name="Navi-Xtreme Media Portal" addr="http://navix.turner3d.net/wiilist/" />
<link name="SHOUTcast Radio" addr="http://www.wiimc.org/media/shoutcast_radio.php" />
<link name="SHOUTcast TV" addr="http://www.wiimc.org/media/shoutcast_tv.php" />
<link name="YouTube" addr="http://www.wiimc.org/media/youtube.php" />
<link name="YouTube - Search" type="search" addr="http://www.wiimc.org/media/youtube.php?q=" />

<folder name="Radio">
<link name="Radio Plus" addr="http://radioplus.dnsalias.org:8000/listen.pls" />
<link name="Otvoreni radio" addr="http://82.193.201.234:8001/listen.pls" />
<link name="Woxy radio" addr="http://woxy.lala.com/stream/aac32.pls" />
</folder>

</file>
```
  1. Add a new line before the `</file>` like this:
```
<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/public/" />
```

The complete file after the change should look like this one:
```
<?xml version="1.0" encoding="utf-8"?>
<file app="WiiMC" version="1.0.7">
<link name="Navi-Xtreme Media Portal" addr="http://navix.turner3d.net/wiilist/" />
<link name="SHOUTcast Radio" addr="http://www.wiimc.org/media/shoutcast_radio.php" />
<link name="SHOUTcast TV" addr="http://www.wiimc.org/media/shoutcast_tv.php" />
<link name="YouTube" addr="http://www.wiimc.org/media/youtube.php" />
<link name="YouTube - Search" type="search" addr="http://www.wiimc.org/media/youtube.php?q=" />

<folder name="Radio">
<link name="Radio Plus" addr="http://radioplus.dnsalias.org:8000/listen.pls" />
<link name="Otvoreni radio" addr="http://82.193.201.234:8001/listen.pls" />
<link name="Woxy radio" addr="http://woxy.lala.com/stream/aac32.pls" />
</folder>

<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/public/" />
</file>
```