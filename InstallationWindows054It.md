# Introduzione #

Questa guida permette di installare VLCShares 0.5.4 in un sistema Windows supportato.



# Metodo 1: installazione automatica #

## Note ##

L'installer per Windows di VLCShares è basato sull'installer di EasyPHP 5.3.3i, le uniche modifiche apportate riguardano alcune configurazioni relative al server Apache e Php. Se volete maggiori dettagli sulle modifiche apportate, leggete le istruzioni presentate nel **`Metodo 2`**.

## Procedura ##

Eseguite il download del [file di installazione di VLCShares 0.5.4](http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4_installer.exe).

Una volta completato il download, avviate il file appena scaricato e seguite le indicazioni a schermo. Durante o all'inizio dell'installazione verranno richiesti `Privilegi Amministrativi` (su Windows Vista e Windows 7 con UAC attivo).

Al termine dell'installazione, potrebbe essere presentato un avviso del Windows Firewall relativo al servizio Apache (come nell'immagine sotto)

![http://www.dsl.uow.edu.au/~sk33/pub/php5-2.png](http://www.dsl.uow.edu.au/~sk33/pub/php5-2.png)

Cliccate sul pulsante "Sblocca"

Procedete quindi con la fase [installazione del software di terze parti](#Installazione_software_di_terze_parti.md)

# Metodo 2: installazione manuale #

## Note ##

Questa guida prevede l'installazione di EasyPHP (versione 5.3.3i, ma dovrebbe funzionare anche con le successive) come base per l'utilizzo di VLCShares.

## Installazione dei requisiti ##

Eseguite il download di una versione di EasyPHP dal sito ufficiale: http://www.easyphp.org/download.php. **(Si consiglia il download della versione 5.3.3i in quanto questa guida è redatta per quella versione, ma una qualsiasi versione successiva dovrebbe funzionare altrettanto bene con questa guida)**

Completate l'installazione seguendo le istruzioni a schermo.

## Configurazione di Apache e PHP ##

Fate doppio click sull'icona di EasyPHP nella SysTray (le icone in basso a destra, nella barra di Windows) per far apparire la finestra principale di EasyPHP.

![http://technology.ohmygoh.com/wp-content/uploads/2009/08/easyphp_changeport.jpg](http://technology.ohmygoh.com/wp-content/uploads/2009/08/easyphp_changeport.jpg)

Cliccate sulla `E`->`Configuration`->`Apache` come in figura.

Si aprirà un editor di testo (di default Notepad) con all'interno il contenuto del file `Httpd.conf`, il file di configurazione di Apache appunto.

Cercate la riga:

```
Listen 127.0.0.1:80
```

e modificatela in

```
Listen 0.0.0.0:80
```

Cercate poi la riga:

```
#LoadModule rewrite_module modules/mod_rewrite.so
```

e modificatela (togliendo il cancelletto) in

```
LoadModule rewrite_module modules/mod_rewrite.so
```

Cercate poi

```
<IfModule alias_module>
    #
    # Redirect: Allows you to tell clients about documents that used to 
    # exist in your server's namespace, but do not anymore. The client 
    # will make a new request for the document at its new location.
    # Example:
    # Redirect permanent /foo http://localhost/bar
```

e aggiungete subito dopo queste righe:

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

Il risultato finale dovrebbe essere questo:

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

Salvate il file e chiudete l'editor di testo.

Nel caso in cui abbiate difficoltà nella modifica del file httpd.conf potete procedere al download di una versione del file gia modificata per la versione di EasyPHP 5.3.3i e sostituirla alla vostra. Scaricate il file http://vlc-shares.googlecode.com/files/httpd_0.5.4.conf, rinominatelo in `httpd.conf` e sovrascrivete il file presente nella directory `EASYPHP\conf_files\httpd.conf`.


Procediamo quindi con la configurazione di PHP.

Sempre dalla finestra principale di EasyPHP, cliccate su `E`->`Configuration`->`PHP Extension`

Si aprirà una finestra con l'elenco delle estensioni di PHP attive (come nell'immagine sotto)

http://2.bp.blogspot.com/_jGEuU1zFjq8/R_ZZdbfEJhI/AAAAAAAAAKE/pbZeAbu8_Xg/s320/EasyPHP+curl.JPG

Aggiungete, se mancante, la `V` vicino ai seguenti moduli:

  * `php_pdo_sqlite`
  * `php_openssl`
  * `php_sqlite`
  * `php_sqlite3`
  * `php_curl`
  * `php_mcrypt` (se presente)

Al termine delle modifiche cliccate su Applica (Apply) e poi su Chiudi

## Installazione dei file ##

Terminata la fase preparatoria, possiamo passare alla vera installazione di VLCShares. Scaricate l'archivio per l'installazione manuale di VLCShares [tramite questo link](http://vlc-shares.googlecode.com/files/vlc-shares_0.5.4.zip)

Decomprimete il contenuto nell'archivio all'interno della directory di `EASYPHP` (verrà creata automaticamente la cartella `vlc-shares/`).

## Installazione di Zend Framework ##

Scaricate Zend Framework (versione 1.10.6 o successiva): potete usare [questo link diretto per la versione 1.10.6](http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.zip) oppure andare all'indice delle release e scaricare la versione che preferite [tramite questo link](http://framework.zend.com/download/latest) (è sufficiente la versione `Minimal Package`).

Decomprimete il contenuto dell'archivio in una posizione facilmente raggiungibile, ad esempio il desktop.

Spostate la directory `library/Zend` all'interno della directory `EASYPHP\vlc-shares\library\`.

Dopo lo spostamento, dovreste ottenere un risultato come questo:

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


# Installazione software di terze parti #

VLCShares utilizza altro software per poter funzionare. In questa fase procederemo all'installazione delle applicazioni necessarie per rendere operative alcune funzionalità. Nello specifico:
  * **VLC**: consente di usufruire delle funzionalità di transcodifica dei filmati
  * **FFMPEG**: viene utilizzato per ottenere informazioni sui video (come il formato e il tipo delle traccie contenute) e per la generazione dei file di anteprima. Funzioni come "Sottotitoli integrati", "Tracce audio multiple" e "Selezione automatica del profilo di transcodifica" sono disponibili solo se FFMPEG è installato e correttamente configurato.
  * **SOPCAST**: viene utilizzato per poter visualizzare i canali trasmessi tramite il protocollo SOP.
  * **RTMPDUMP**: viene utilizzato per poter visualizzare gli streaming di tipo RTMP, solitamente trasmissioni live.

## Installazione di VLC ##

Scaricate il file di installazione di VLC dal [sito ufficiale](http://www.videolan.org/vlc/) e seguite le informazioni a schermo. E' sempre consigliata l'ultima versione disponibile.

## Installazione di FFMpeg ##

Scaricate FFMPEG per Windows (versione static build) http://ffmpeg.arrozcru.org/autobuilds/ffmpeg-latest-mingw32-static.7z.

Decomprimetela in una posizione a vostra scelta (l'importante è che vi ricordiate dove).

## Installazione di Sop Cast (Opzionale) ##

Scaricate l'ultima versione di Sop Cast disponibile dal [sito ufficiale](http://www.sopcast.org/download). Procedete all'installazione seguendo le informazioni a schermo

## Installazione di RTMPDump (Opzionale) ##

Scaricate l'ultima versione di RTMPDump per Windows disponibile dal [sito ufficiale](http://rtmpdump.mplayerhq.hu/download).

Attualmente l'ultima versione disponibile è la 2.3 disponibile tramite [questo indirizzo](http://rtmpdump.mplayerhq.hu/download/rtmpdump-2.3-windows.zip). Con l'introduzione del protocollo di autenticazione di Tipo 9 da parte di Adobe, alcuni link potrebbero non essere visibili affatto (quelli che richiedono esclusivamente questa versione dell'autenticazioni) o essere visualizzabili solo con una versione precedente alla 2.1d (a causa di un bug in RTMPDump). Nel caso alcuni stream vi diano problemi, provate quindi ad utilizzare la versione 2.1d prima di abbandonare ogni speranza in attesa che RTMPDump introduca il supporto al nuovo tipo di antenticazione.

Decomprimete il contenuto dell'archivio scaricato in una posizione a vostra scelta (l'importante è che vi ricordiate dove).


# E dopo? #

Al termine procedete con l'ultimazione dell'installazione tramite il browser seguendo [la guida alla configurazione](PostInstallConfiguration054It.md).