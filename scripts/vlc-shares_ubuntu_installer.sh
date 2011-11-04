#!/bin/bash

#Installer for vlc-shares on ubuntu 11.04

# All temporary files will be downloaded inside this directory
TMPDIR="/tmp/vlc-shares-installer"
# vlc-shares will be installed here
DESTDIR="/opt"

# packages requirements
REQS="\
	apache2 \
	libapache2-mod-php5 \
	php5 \
	zend-framework \
	zend-framework-bin \
	vlc \
	php5-sqlite \
	php5-mcrypt \
	php5-cli \
	vlc \
	ffmpeg \
	libavcodec-extra-52 \
"

# sopcast related
ENABLESOPCAST=1
SOPCASTPPA="ppa:jason-scheunemann/ppa"
SOPCAST_REQS="sp-auth"

# rtmpdump related
ENABLERTMPDUMP=1
RTMPDUMPPPA=""
RTMPDUMP_REQS="rtmpdump"

# download url, hashes and filename about vlc-shares core package
DDL_VLCSHARES_CORE="http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4.zip"
FILE_VLCSHARES_CORE="${TMPDIR}/vlc-shares.zip"

# download url filename about vlc-shares http configs
DDL_VLCSHARES_APACHECONF="http://vlc-shares.googlecode.com/svn-history/r570/trunk/scripts/apacheconf_ubuntu1104.conf"
FILE_VLCSHARES_APACHECONF="${TMPDIR}/vlc-shares.conf"


JUMP=$1
UNINSTALL=0

if [ $JUMP ]; then
	if [ ! $(echo "$JUMP" | grep -E "^[0-9]+$") ]; then
		if [ $JUMP = "uninstall" ]; then
			UNINSTALL=1
		else
			UNINSTALL=0
			JUMP=0
		fi
	fi
else
	JUMP=0
fi

############ UNINSTALL

if [ $UNINSTALL -eq 1 ]; then
	
	echo ---------- Removing apache configs ---------- 
	sudo rm -i /etc/apache2/conf.d/vlc-shares.conf
	
	echo ---------- Removing vlc-shares core files ---
	sudo rm -RI "${DESTDIR}/vlc-shares/"
	
	echo ---------- Removing link in /var/www/ -------
	sudo rm -i /var/www/vlc-shares
	
	echo ---------- Cleaning temp files --------------
	sudo rm -Ri $TMPDIR
	
	echo ---------------------------------------------
	echo
	echo
	echo VLCShares removed. Bye
	exit;
	
fi

############ ENDUNINSTALL

set -e

if [ $JUMP -lt 1 ]; then

	echo ---------- updating apt ---------------------
	
	sudo apt-get update
	
	echo ---------- installing requirements ----------
	
	sudo apt-get install $REQS

fi

if [ $JUMP -lt 2 ]; then	
	if [ $ENABLESOPCAST -eq 1 ]; then
		echo ---------- SOPCAST installation -------------
	
		echo -n "Install SOPCAST? (y/n) "
		read response
	
		if [ "$response" = "y" ]; then
			if [ "$SOPCASTPPA" != "" ]; then
				echo ---------- SOPCAST installation: preparing --
				sudo apt-add-repository $SOPCASTPPA
				sudo apt-get update
			fi
			echo ---------- SOPCAST installation: installing -
			sudo apt-get install $SOPCAST_REQS
		fi
	fi
fi

if [ $JUMP -lt 3 ]; then
	if [ $ENABLERTMPDUMP -eq 1 ]; then
		echo ---------- RTMPDUMP installation ------------
	
		echo -n "Install RTMPDUMP? (y/n) "
		read response
	
		if [ "$response" = "y" ]; then
			if [ "$RTMPDUMPPPA" != "" ]; then
				echo ---------- RTMPDUMP installation: preparing -
				sudo apt-add-repository $RTMPDUMPPPA
				sudo apt-get update
			fi
			echo ---------- RTMPDUMP installation: installing 
			sudo apt-get install $RTMPDUMP_REQS
		fi
	fi
fi

if [ $JUMP -lt 4 ]; then
	mkdir -p $TMPDIR
	
	echo ---------- Downloading files ----------------
	
	#download vlc-shares core pkg
	wget -O $FILE_VLCSHARES_CORE $DDL_VLCSHARES_CORE
	
	#download apache config file
	wget -O $FILE_VLCSHARES_APACHECONF $DDL_VLCSHARES_APACHECONF
	
	echo ---------- Moving apache config file --------
	
	sudo a2enmod rewrite
	sudo mv $FILE_VLCSHARES_APACHECONF /etc/apache2/conf.d/vlc-shares.conf
	sudo chmod 644 /etc/apache2/conf.d/vlc-shares.conf 
	
	echo ---------- Unpacking vlc-shares core --------
	
	sudo unzip $FILE_VLCSHARES_CORE -d $DESTDIR
	sudo ln -s /usr/share/php/libzend-framework-php/Zend "${DESTDIR}/vlc-shares/library/Zend"
fi

if [ $JUMP -lt 5 ]; then
	# Link not needed anymore, Alias in httpd conf
	echo ---------- Linking vlc-shares in doc root ---
	#sudo ln -s "${DESTDIR}/vlc-shares/public" /var/www/vlc-shares
fi

if [ $JUMP -lt 6 ]; then
	echo ---------- Fixing permissions ---------------
	
	sudo chown www-data:www-data "${DESTDIR}/vlc-shares/"
	sudo chmod 777 -R "${DESTDIR}/vlc-shares/"
	
	echo ---------- Restarting apache ----------------
	sudo /etc/init.d/apache2 restart
fi

if [ $JUMP -lt 6 ]; then
	echo ---------- Cleaning temp files --------------
	sudo rm -Ri $TMPDIR
fi

echo ---------------------------------------------
echo
echo
echo Now open your browser to http://localhost/vlc-shares

