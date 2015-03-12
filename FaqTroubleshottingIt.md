


# FAQ #

## Cosa fa di preciso VLCShares? ##

VLCShares è una interfaccia web per VLC, consente di navigare attraverso risorse di diverso tipo, tramite i vari plugin, e di renderle fruibili  a diversi tipi di dispositivi.

## Quali sono i dispositivi supportati? ##

Ufficialmente VLCShares è nato per essere utilizzato tramite WiiMC, nel corso dello sviluppo è stato anche aggiunto il supporto per i dispositivi Android tramite l'interfaccia Web e alcuni profili di transcodifica ad-hoc.

## A cosa serve installare FFMpeg? ##

FFMpeg viene utilizzato per ottenere informazioni sui file che verranno transcodificati, permettendo di scegliere il profilo di transcodifica più indicato. Inoltre alcune funzionalità, come i sottotitoli integrati, le tracce audio multiple e le immagini di anteprima dei file nelle cartelle condivise, fanno affidamento su FFMpeg per ottenere le informazioni necessarie.

## Posso usare VLCShares con Lighttpd? ##

Ufficialmente non è supportato, ma alcuni utenti mi hanno confermato che è possibile farlo. Se qualcuno volesse aggiungere questa configurazione fra quelle supportate, sarei ben lieto di farlo a patto che qualcuno si occupi della scrittura della documentazione e del testing su questo Web Server.
Se volete maggiori informazioni su VLCShares + Lighttpd vi consiglio di leggere il thread sul forum: http://www.wiimc.org/forum/viewtopic.php?f=8&t=1211

## Posso usare VLCShares su Fedora? ##

Anche in questo caso, non c'è supporto ufficiale ne testing, ma alcuni utenti mi hanno confermato che è possibile farlo. Se qualcuno volesse aggiungere questa configurazione fra quelle supportate, sarei ben lieto di farlo a patto che qualcuno si occupi della scrittura della documentazione e del testing su questo sistema. Se volete maggiori informazioni su VLCShares + Fedora vi consiglio di leggere il thread sul forum http://www.wiimc.org/forum/viewtopic.php?f=4&t=562&start=90#p3514 (parzialmente in lingua italiana)

## Posso usare VLCShares su Mac? ##

Leggi sopra... stessa questione. Però in questo caso non c'è un thread sul forum :P

## Quali funzionalità possono essere aggiunte? ##

C'è un limite alla fantasia? In linea di massima, qualsiasi sito web o hoster che consenta di ottenere (anche se non ufficialmente) un indirizzo di uno stream audio/video può essere incluso all'interno di VLCShares. Per maggiori informazioni potete fare riferimento alla guida di sviluppo per i Plugin.

## Cosa verrà aggiunto nelle prossime versioni? ##

Francamente non ne ho idea. Non so nemmeno se e quando ci saranno versioni successive. Dipende tutto da se trovo qualcosa che mi interesserebbe aggiungere. VLCShares è un progetto che sviluppo per utilizzarlo personalmente. Se avete proposte, siete liberi di provare a stuzzicare il mio interesse. Altrimenti, potete provare a realizzare voi qualcosa.



# Troubleshotting #

## Indipendentemente da quale sia il tuo problema... ##

Prima di qualsiasi cosa, vai nella pagina `Test di Sistema` e assicurati che tutto sia verde o blu. Se ci sono Warning o Errori correggili. Molte volte i problemi sono causati da queste cose.

## Clicco su "Avvia transcodifica" ma non riesco a vedere il video ##

Come prima cosa, verifica che il tuo percorso a VLC sia corretto (deve includere il nome del file, non solo la directory).

Se stai usando un dispositivo Android, assicurati che venga riconosciuto come tale. Cliccando su `Metodi di trasmissione` dovrebbe apparire il metodo `Android Output (RTP Stream)` come primo. Se non appare, [guarda sotto](#Ho_un_dispositivo_Android,_ma_VLCShares_non_lo_riconosce.md).

Se invece il telefono viene rinosciuto normalmente, prova ad utilizzare un player alternativo a quello installato di Default. Ci sono tanti player gratuiti nell'android Market. Personalmente uso Real Player, ma potete scegliere quello che preferite a patto che supporti gli stream RTP.

## Ho un dispositivo Android, ma VLCShares non lo riconosce ##

Il riconoscimento dei dispositivi Android avviene analizzando la stringa `User-Agent` che il dispositivo invia a VLCShares. Se contiene la parola `Android` allora il dispositivo è riconosciuto come tale.

Purtroppo il browser di Android consente, tramite le opzioni, di disattivare la modalità `Mobile View`. Disattivandola, il dispositivo tenta di camuffarsi come un normale Browser per PC e non invia più il normale `User-Agent`. Chiaramente, in questo caso, VLCShares non può riconoscerlo.

Riattivate la modalità `Mobile View` e tutto funzionerà come deve.

## Il mio video va a scatti, l'audio non è sincronizzato... ##

La transcodifica è un task che richiede molte risorse di sistema. Chiaramente, la potenza del PC influenza in maniera evidente i risultati e la qualità della transcodifica. Non pensate nemmeno di poter transcodificare un video 1080p con un Pentium 2 (a dire la verità sarei molto sorpreso se riusciste anche solo a guardarlo direttamente :O).

Inoltre, giusto per lavarmi le mani, la fase di transcodifica è affidata esclusivamente a VLC. Se avete problemi rivolgetevi al forum di VLC. L'unica responsabilità di VLCShares è quella di creare una stringa per lanciare VLC e controllare che sia attivo o meno. Inoltre, i parametri utilizzati per la transcodifica potete modificarli a vostro piacimento dalla pagina di gestione del plugin `Profili`. E' tutto nelle vostre mani. Sperimentate e, se avete successo o dubbi, condividete le vostre scoperte sul forum. Sarò lieto di discuterne con voi e aggiungere eventuali modifiche a VLCShares.

## La transcodifica non sempre funziona con Megavideo ##

Purtroppo è un problema noto e a cui non so come porvi rimedio. Per alcuni video, pare che Megavideo rifiuti la connessione da parte di VLC. Pare inoltre che questo problema si verifichi soltanto per gli utenti NON-Premium.

## Dopo circa 70 minuti, i video da Megavideo si bloccano ##

Mi dispiace, lamentati con Megavideo per il loro blocco dopo 72 minuti di visione per gli utenti NON-Premium.

## Wiimc: perchè non posso andare avanti e indietro? ##

WiiMC ha disabilitato la possibilità di spostarsi avanti e indietro negli stream Http (quelli usati da VLCShares). Devo ammettere inoltre che condivido la loro politica in quanto, spesso, spostarti avanti e indietro portava al blocco della riproduzione. Non sempre è possibile il seeking.
Se volete provare ad aggirare il blocco, usate la modalità transcodificata e utilizzate i controlli di riproduzione di VLCShares per andare avanti e indietro.

## Problemi di permessi su Windows Vista/7 ##

Per poter installare i plugin aggiuntivi, è necessario che VLCShares abbia permessi in lettura e scrittura su tutti i suoi file. Dalla versione 0.5.4, usando l'installer per Windows i permessi dovrebbere essere impostati automaticamente. Nel caso questo non succeda o state usando una versione manuale, potete usare questi semplici passi per correggere il problema:

  1. Aprire risorse del computer ed andare nella directory di installazione di vlc-shares (solitamente C\Program Files (x86)\VLCShares\)
  1. Cliccate con il tasto destro sulla cartella  `vlc-shares\` (o `www\vlc-shares\` se usate VLCShares 0.5.3 o precedenti) e selezionate "Proprietà"
  1. Cliccate sul tab Sicurezza (nella parte alta della finestra che si è aperta), ottenendo una finestra simile a questa: ![http://www.blogsdna.com/wp-content/uploads/2009/01/windows-7-files-and-folder-security-tab.png](http://www.blogsdna.com/wp-content/uploads/2009/01/windows-7-files-and-folder-security-tab.png)
  1. Cliccate sul pulsante "Modifica"
  1. Selezionate l'utente "Users (Windows\Users)" o una sua eventuale traduzione italiana "Utenti (Windows\Users)"
  1. Mettete la V su "Permetti" in corrispondenza della riga "Controllo completo" (o qualcosa di analogo, non ricordo la traduzione italiana. I ogni caso è il primo della lista)
  1. Se nella lista degli utenti c'e' anche il nome del vostro utente (qualcosa tipo "ILMIONOME (Windows\ILMIONOME)" selezionate anche lui ed eseguite nuovamente il passaggio precedente
  1. Cliccate su OK
  1. Tornate alla finestra "Proprietà" e selezionate la scheda Generale
  1. Verificate che la casella "Sola lettura" non sia selezionata
  1. Cliccate Ok per terminare