# Introduction #

This guide help you to install VLCShares 0.5.4 on Ubuntu 10.10, 11.04 and 11.10 (deb installer is experimental).



# Method 1: automatic installation #

## Notes ##

You will be asked to install RTMPDump and SopCast in the installation procedure. SopCast will be installed using the PPA repository [ppa:jason-scheunemann/ppa](https://launchpad.net/~jason-scheunemann/+archive/ppa). If you don't want to use this repository, reply No.

## Procedure ##

Open a Terminal (Applications->Accessories->Terminal) and write inside it

### For Ubuntu 10.10 ###

```
wget http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_ubuntu-installer.sh
```

After download is completed, type in the Terminal (allowing execution):

```
chmod a+x vlc-shares_0.5.4_ubuntu-installer.sh
```

After that, start the installation typing:

```
./vlc-shares_0.5.4_ubuntu-installer.sh
```

Follow on-screen instructions. On operation completed, move on [post installation guide](PostInstallConfiguration054En.md).


### For Ubuntu 11.04 ###

```
wget -O vlc-shares_0.5.4_ubuntu-installer.sh http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_ubuntu-11.04-installer-2.sh
```


After download is completed, type in the Terminal (allowing execution):

```
chmod a+x vlc-shares_0.5.4_ubuntu-installer.sh
```

After that, start the installation typing:

```
./vlc-shares_0.5.4_ubuntu-installer.sh
```

Follow on-screen instructions. On operation completed, move on [post installation guide](PostInstallConfiguration054En.md).

### For Ubuntu 11.10 ###

```
wget https://vlc-shares.googlecode.com/files/vlc-shares_0.5.4-1_all.deb
```

After that, start the installation typing:

```
sudo dpkg -i vlc-shares_0.5.4-1_all.deb
sudo apt-get install -f
```

On operation completed, move on [post installation guide](PostInstallConfiguration054En.md).


# Method 2: manual installation #

## Requirements installation ##

Open a Terminal (Applications->Accessories->Terminal) and type inside it:

**For Ubuntu 10.10 and 11.04**:
```
sudo apt-get update && sudo apt-get install apache2 libapache2-mod-php5 php5 zend-framework zend-framework-bin vlc php5-sqlite php5-mcrypt php5-cli vlc ffmpeg libavcodec-extra-52 libavcodec-unstripped-52
```

**For Ubuntu 11.10**:
```
sudo apt-get update && sudo apt-get install apache2 libapache2-mod-php5 php5 zend-framework zend-framework-bin vlc php5-sqlite php5-mcrypt php5-cli vlc ffmpeg libavcodec-extra-53
```


## Apache configuration ##

Enable `mod_rewrite` typing:

```
sudo a2enmod rewrite
```

After that type in sequence:

```
cd /etc/apache2/conf.d/
```

and

**For Ubuntu 10.10**:
```
sudo wget -O vlc-shares.conf http://vlc-shares.googlecode.com/svn-history/r494/trunk/scripts/apacheconf_ubuntu1010.conf
sudo chmod 644 vlc-shares.conf
```

**For Ubuntu 11.04 and 11.10**:
```
sudo wget -O vlc-shares.conf http://vlc-shares.googlecode.com/svn-history/r570/trunk/scripts/apacheconf_ubuntu1104.conf
sudo chmod 644 vlc-shares.conf
```

After that, type:
```
sudo gedit /etc/apache2/sites-enabled/000-default
```

Scroll down the file until you see the part `<Directory /var/www/>`. Underneath that line of code, change `AllowOverride None` to `AllowOverride All`

![http://imagecdn.maketecheasier.com/2011/02/vlc-shares-edit-apache-config.png](http://imagecdn.maketecheasier.com/2011/02/vlc-shares-edit-apache-config.png)

Save and close the file.

Let's do a Apache service restart

```
sudo /etc/init.d/apache2 restart
```

## File installation ##

Move inside the `/opt` directory, typing

```
cd /opt
```

and download the VLCShares manual installation package:

```
sudo wget -O vlc-shares.zip http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4.zip
```

Now, unpack it

```
unzip vlc-shares.zip -d .
```

and fix the permission of the directory created right now:

```
sudo chown www-data:www-data -R vlc-shares/
sudo chmod 777 -R vlc-shares/
```

**Only UBUNTU 10.10 users** have to create a symbolic link to the folder `/opt/vlc-shares/public/` into `/var/www` with this command

```
sudo ln -s /opt/vlc-shares/public /var/www/vlc-shares
```

Then create one to the Zend Framework library in `/opt/vlc-shares/library` typing

```
sudo ln -s /usr/share/php/libzend-framework-php/Zend /opt/vlc-shares/library/Zend
```

## Sop Cast installation (optional) ##

Sop Cast installation uses the PPA repository [ppa:jason-scheunemann/ppa](https://launchpad.net/~jason-scheunemann/+archive/ppa). If you don't want to use this repository, search for another source of Sop Cast and move to the next step.

Tramite un Terminale, digitate in sequenza
```
sudo apt-add-repository ppa:jason-scheunemann/ppa
sudo apt-get update
sudo apt-get install sp-auth
```

## RTMPDump installation (optional) ##

RTMPDump is available to install from official Ubuntu universe repository. To install it, just type

```
sudo apt-get install rtmpdump
```

## After that... ##

Move on the [post installation guide](PostInstallConfiguration054En.md).