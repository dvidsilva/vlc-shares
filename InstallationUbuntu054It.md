# Introduzione #

Questa guida permette di installare VLCShares 0.5.4 in un sistema Ubuntu 10.10 e 11.04 (e 11.10, ma solo installazione manuale).



# Metodo 1: installazione automatica #

## Note ##

Durante l'installazione vi verrà richiesto se procedere anche all'installazione RTMPDump e SopCast. L'installazione di SopCast avviene utilizzando il PPA [ppa:jason-scheunemann/ppa](https://launchpad.net/~jason-scheunemann/+archive/ppa).

## Procedura ##

Aprite una Terminale (Applicationi->Accessori->Terminale) e digitate all'interno:

**Per Ubuntu 10.10**:
```
wget http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_ubuntu-installer.sh
```

**Per Ubuntu 11.04**:
```
wget -O vlc-shares_0.5.4_ubuntu-installer.sh http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_ubuntu-11.04-installer-2.sh
```

Attendete il termine del download e scrivete per dare permessi di esecuzione:

```
chmod a+x vlc-shares_0.5.4_ubuntu-installer.sh
```

Avviate quindi l'installazione:

```
./vlc-shares_0.5.4_ubuntu-installer.sh
```

Seguite le informazioni a schermo. Al termine procedete con l'ultimazione dell'installazione tramite il browser seguendo [la guida alla configurazione](PostInstallConfiguration054it.md).


# Metodo 2: installazione manuale #

## Installazione dei requisiti ##

Aprite una Terminale (Applicationi->Accessori->Terminale) e digitate all'interno:

**Per Ubuntu 10.10 and 11.04**:
```
sudo apt-get update && sudo apt-get install apache2 libapache2-mod-php5 php5 zend-framework zend-framework-bin vlc php5-sqlite php5-mcrypt php5-cli vlc ffmpeg libavcodec-extra-52 libavcodec-unstripped-52
```

**Per Ubuntu 11.10**:
```
sudo apt-get update && sudo apt-get install apache2 libapache2-mod-php5 php5 zend-framework zend-framework-bin vlc php5-sqlite php5-mcrypt php5-cli vlc ffmpeg libavcodec-extra-53
```


## Configurazione di Apache ##

Attivate `mod_rewrite` digitando:

```
sudo a2enmod rewrite
```

quindi digitate in sequenza nel terminale

```
cd /etc/apache2/conf.d/
```

e

**Per Ubuntu 10.10**:
```
sudo wget -O vlc-shares.conf http://vlc-shares.googlecode.com/svn-history/r494/trunk/scripts/apacheconf_ubuntu1010.conf
sudo chmod 644 vlc-shares.conf
```

**Per Ubuntu 11.04 e 11.10**:
```
sudo wget -O vlc-shares.conf http://vlc-shares.googlecode.com/svn-history/r570/trunk/scripts/apacheconf_ubuntu1104.conf
sudo chmod 644 vlc-shares.conf
```

Ora, scrivete:
```
sudo gedit /etc/apache2/sites-enabled/000-default
```

Spostatevi in basso nel file fino a trovare la parte con `<Directory /var/www/>`. All'interno di quella porzione, cambiate `AllowOverride None` in `AllowOverride All`

![http://imagecdn.maketecheasier.com/2011/02/vlc-shares-edit-apache-config.png](http://imagecdn.maketecheasier.com/2011/02/vlc-shares-edit-apache-config.png)

Salvate e chiudete il file.


Procedete quindi con un riavvio di Apache

```
sudo /etc/init.d/apache2 restart
```

## Installazione dei file ##

Sempre nel terminale, posizionatevi nella cartella `/opt`

```
cd /opt
```

e scaricate l'archivio di vlc-shares con il comando:

```
sudo wget -O vlc-shares.zip http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4.zip
```

Ora decomprimetelo

```
unzip vlc-shares.zip -d .
```

e correggete i permessi della directory appena creata:

```
sudo chown www-data:www-data -R vlc-shares/
sudo chmod 777 -R vlc-shares/
```

**Solo gli utenti Ubuntu 10.10** devono anche creare un link simbolico alla directory `/opt/vlc-shares/public/` in `/var/www` con il comando

```
sudo ln -s /opt/vlc-shares/public /var/www/vlc-shares
```

Createne uno anche alla directory della libreria Zend Framework in `/opt/vlc-shares/library`

```
sudo ln -s /usr/share/php/libzend-framework-php/Zend /opt/vlc-shares/library/Zend
```

## Installazione di Sop Cast (Opzionale) ##

L'installazione di Sop Cast richiede l'aggiunta del repository PPA PPA [ppa:jason-scheunemann/ppa](https://launchpad.net/~jason-scheunemann/+archive/ppa).

Tramite un Terminale, digitate in sequenza
```
sudo apt-add-repository ppa:jason-scheunemann/ppa
sudo apt-get update
sudo apt-get instal sp-auth
```

## Installazione di RTMPDump (Opzionale) ##

RTMPDump è compreso nei repository ufficiali di Ubuntu 10.10. Per installarlo basta quindi digitare nel terminale

```
sudo apt-get install rtmpdump
```

## Al termine... ##

Procedete con l'ultimazione dell'installazione tramite il browser seguendo [la guida alla configurazione](PostInstallConfiguration054it.md).