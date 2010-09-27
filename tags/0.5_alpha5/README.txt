#################################################################
# __      ___       _____       _____ _                         #
# \ \    / / |     / ____|     / ____| |                        #
#  \ \  / /| |    | |   ______| (___ | |__   __ _ _ __ ___ ___  #
#   \ \/ / | |    | |  |______|\___ \| '_ \ / _` | '__/ _ | __| #
#    \  /  | |____| |____      ____) | | | | (_| | | |  __|__ \ #
#     \/   |______|\_____|    |_____/|_| |_|\__,_|_|  \___|___/ #
#     															#
#################################################################

VLC-SHARES v0.4.1 - 20/07/2010
Creato da Francesco Capozzo (Ximarx)
ximarx@gmail.com

Tutto il contenuto di questo archivio viene rilasciato utilizzando
licenza GPL v3 (http://www.gnu.org/licenses/gpl.html)

Vlc-shares permette di controllare lo streaming di file tramite vlc
utilizzando l'interfaccia di WiiMc e permettendo la riproduzione
di video HD senza dover interagire direttamente con il VLC.

Altre informazioni sono disponibili qui 
http://ximarx.netsons.org/blog/vlc-share/

Sommario:
PPP) Prerequisiti
1) Installazione & Configurazione
2) Configurazione Wii
3) Changelog
4) Possibili sviluppi futuri
5) Problemi noti
6) Soluzione per problemi comuni
7) Ringraziamenti


/==========---
| PPP: Prerequisiti
\==========---

VLC-Share è stati testati su:
 - Ubuntu 10.04
 - Windows XP Pro SP3
 
I requisiti per l'utilizzo in abiente Linux Ubuntu 10.04
 - Apache 2.2 (testato con la versione 2.2.14 presente nei repository ufficiali)
 - mod_rewrite (fornito insieme alla versione di Apache 2 disponibile nei repository ufficiali)
 - PHP 5.3+ (testato con la versione presente nei repository ufficiali)
 - VLC 1.0.6+ (testato con la versione presente nei repository ufficiali)
 - netcap (testato con la versione presente nei repository ufficiali)
 - Zend Framework 1.10.3 (testato con la versione presente nei repository ufficiali)
 - php5-sqlite (testato con versione presente nei repository ufficiali)
 
I requisiti per l'utilizzo in ambiente Windows XP Pro SP3
 - EasyPhp 5.3.2i+ (http://sourceforge.net/projects/quickeasyphp/files/EasyPHP/5.3.2i/EasyPHP-5.3.2i-setup.exe/download)
 - Zend Framework 1.10.6 minimal (http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.zip)
 - VLC 1.1.0+ (http://sourceforge.net/projects/vlc/files/1.1.0/win32/vlc-1.1.0-win32.exe/download)
 - netcat for windows (fornito con vlc-shares)
 - sysinternals PsExec (fornito con vlc-shares)
 - taskkill & tasklist (presente in tutte le versioni Professional di Windows XP)

/==========---
| 1: Installazione & Configurazione
\==========---

La guida all'installazione:
http://ximarx.netsons.org/blog/vlc-share/guida-installazione-e-configurazione/

Seleziona il file di configurazione per il tuo sistema (Linux o Windows) e
rinominalo in vlc-shares.config.ini e poi divertiti a modificarlo :P

/==========---
| 2: Configurazione Wii (WiiMc)
\==========---

Per permettere a WiiMC di utilizzare le collezioni condivise da Vlc-Shares
e' necessario aggiungere una nuova sorgente all'interno del file
onlinemedia.xml contenuto nella directory di WiiMc
aggiungendo 

<link name="Collezioni VLC-Shares" addr="http://INDIRIZZO/vlc-shares/public/" />
	
sopra la riga finale del file (</file>), dove INDIRIZZO indica l'indirizzo
IP o l'hostname del server su cui e' installato Apache.
(Attenzione: se avete utilizzato configurazioni particolari per Apache, ricordate
di adattare l'indirizzo alla vostra configurazione. Questo e' solo un esempio)

/==========---
| 3: Changelog
\==========---

*** Versione 0.4.1 (20/7/2010) ***
 - aggiunto plugin per libreria Megavideo
 - aggiunto plugin di conversione PLX->HTML se si sta guardando la collection con il browser
 - aggiunto plugin per il supporto a terminali android (testato con Motorola Milestone)
 - aggiunta pagina principale di gestione (http://IP_ADDRESS/vlc-shares/public)

*** Versione 0.4 (15/7/2010) ***
 - aggiunto plugins system
 - codice riscritto quasi interamente
 - aggiunte diverse modalita' per comandare vlc (Commander RC o Commander HTTP)
 - la versione per windows e' notevolmente piu' veloce (se viene utilizzato Commander HTTP)
 - aggiunto plugin per non visualizzare i file nascosti
 - voci opzionali visualizzate configurabili tramite il file di configurazione

*** Versione 0.3.2 (8/7/2010) ***
 - compatibilita' con configurazioni di apache su porta diversa da 80 (questa volta funziona)
 - aggiunta una pagina di check dello stato di sistema: http://YOUR_IP_ADDRESS/vlc-shares/public/test
 - aggiunta una configurazione per il debug
 

*** Versione 0.3.1 (8/7/2010) ***
 - compatibilita' con configurazioni di apache su porta diversa da 80
  

*** Versione 0.3 (7/7/2010) ***
 - compatibilita' con EasyPhp per Windows
 - aggiunto un collegamento per tornare agli indici delle collezioni alle pagine di navigazione
 - riscritto il file di configurazione per renderlo piu facilmente modificabile nelle parti riguardanti vlc
 

*** Versione 0.2 (7/7/2010) ***
 - aggiunta la possibilita' di mettere in pausa/riprendere la riproduzione
 - aggiunta la possibilita' di spostarsi 5 minuti in avanti/indietro durante la riproduzione
 - visualizzato il tempo totale/corrente di riproduzione
 - e' possibile avviare vlc con l'interfaccia rc per il controllo remoto


*** Versione 0.1 (6/7/2010) ***
 - prima versione rilasciata
 
 
/==========---
| 4: Possibili sviluppi futuri
\==========---
 
Quello che magari un giorno verra' aggiunto se WiiMc continuera' a non supportare
protocolli come UPnP o DLNA

 - possibilita' di alterare lo streaming tramite i controlli di riproduzione
  (Es: mettere in pausa, avanti, dietro, cambiare sottotitoli, cambiare lingua...)
  [PARZIALMENTE FATTO]
 - aggiungere un'interfaccia per la gestione delle collezioni
 - aggiungere un'interfaccia per la modifica delle configurazioni
 - aggiungere il supporto ai filtri famiglia
 - aggiungere il supporto al login/protezione dei contenuti
 
/==========---
| 5: Problemi noti
\==========---

 - se viene indicata una sola collezione condivisa, WiiMc indica "Errore lettura File"
 - selezionando la modalita' di transcodifica nella libreria Megavideo, il video non e'
 	raggiungibile oppure si sente solo l'audio (vlc ha un bug nella gestione dei video
 	h264/aac durante la transcodifica. Megavideo usa proprio questo formato per la 
 	maggior parte dei video. Maggiori info : https://trac.videolan.org/vlc/ticket/2850)
 - selezionando la modalita' diretta nella libreria Megavideo, il video non si vede o
 	va a scatti (La wii non ha abbastanza potenza per la decodifica dei file in h264/aac.
 	Megavideo usa questa codifica per molti file)
 	
  
/==========---
| 6: Soluzioni a problemi comuni
\==========---

 - non ho Zend Framework nella include_path e il programma non funziona:
	basta aggiungere ZF all'include_path per risolvere. Nel caso in cui l'aggiunta
	non sia possibile, basta copiare i file di ZF 1.10.X all'interno della cartella
	vlc-shares/library/	
 - se viene indicata una sola collezione condivisa, WiiMc indica "Errore lettura File":
 	non e' un problema di vlc-shares. Pare che WiiMc non gradisca playlist di 1 solo
 	elemento. Come workaround basta aggiungere una seconda collezione condivisa.
 		
/==========---
| 7: Ringraziamenti
\==========---
 
 - La gente che ha creato VLC
 - La gente che ha creato WiiMc
 - La gente che ha creato Zend Framework
 - La gente che ha creato Apache
 - La gente che ha creato PHP
 - La gente che ha creato PsExec (SysInternals)
 - La gente che ha creato NetCat
 - La gente che ha creato NetCat for Windows
 - luruke, per la classe Megavideo (http://forum.codecall.net/classes-code-snippets/14324-php-megavideo-downloader.html)
 - La gente che ha creato $ringraziamento_value
 
 