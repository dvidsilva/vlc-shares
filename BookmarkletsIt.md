# Premesse #

Questa guida mostra come trarre il massimo profitto dall'utilizzo della Bookmarklet di VLCShares per aggiungere nuovi link alla Online Library e nuove pagine nella Bookmark



# Cosa è la Bookmarklet #

La bookmarklet è un piccolo componente di VLCShares da aggiungere alla barra dei preferiti del browser che ti consente di aggiungere rapidamente nuovi elementi all'interno della Online Library e nuove pagine nei Bookmark di VLCShares

# Preparazione del Browser #

Per poter iniziare ad utilizzare la bookmarklet è sufficiente andare nella pagina di gestione della Online Library di VLCShares e trascinare il pulsante _Aggiungi a VLCShares_ nella barra dei preferiti del browser. Niente di piu semplice.

# Utilizzo #

La bookmarklet può essere utilizzata semplicemente cliccando sul pulsante dei preferiti appena aggiunto mentre ci si trova all'interno di una qualsiasi pagina web. Attraverso l'elemento che apparirà in alto a destra della pagina sarà possibile interagire con la bookmarklet aggiungendo nuovi link alla online library o nuove pagine fra i bookmarks di vlc-shares.

## Aggiungere nuovi link ##

Per aggiungere nuovi link, basterà selezionare l'opzione _Cattura link_. A questo punto tutti i collegamenti presenti nella pagina originale verranno evidenziati con un contorno verde e una piccola icona rappresentante una croce per l'aggiunta.


Sarà possible catturare nuovi link semplicemente cliccando sui collegamenti evidenziati. All'interno del frame della bookmarklet verranno evidenziate informazioni riguardanti i link appena aggiunti (se validi o meno, l'etichetta e l'indirizzo del collegamento catturato). Ovviamente, solo i collegamenti a pagine di hoster di cui è stato installato un plugin in vlc-shares saranno ritenuti validi (Esempio: youtube, sopcast, megavideo, videozer, ...)

Una volta terminata la cattura di tutti i link d'interesse, basterà cliccare sul pulsante "Termina cattura" per poter visualizzare una schermata di riepilogo dei link catturati e confermare i dettagli dei link da utilizzare e la categoria in cui inserirli.

Posizionando il cursore su ogni link, verrà visualizzato un elemento di informazioni ottenute contattando direttamente l'hoster. Sarà possibile indicare di utilizzare quelle informazioni anzicchè quelle ottenute dalla pagina in cui il link era contenuti. I link non validi (perchè collegati a risorse eliminate o non piu disponibili) verranno evidenziati e automaticamente scartati dall'inclusione

Una volta confermati tutti i dati, basterà cliccare sul pulsante _Salva link_ per aggiungere i link alla online library

## Aggiungere nuovi bookmark ##

Un'altra opzione disponibile nella bookmarklet riguarda l'aggiunta di nuove pagine fra i bookmark di vlc-shares. Per aggiungere una nuova pagina basterà selezionare l'opzione _Aggiungi Bookmark_ dalla schermata principale della bookmarklet.

Una volta premuto il pulsante verrà visualizzata una schermata di riepilogo delle informazioni ottenute dalla pagina (titolo, descrizione, immagine di anteprima) e una serie di altre informazioni piu tecniche come lo user-agent utilizzato per visitare la pagina e i cookie relativi alla pagina.

Queste ultime due opzioni permettono a vlc-shares individuare i link anche da pagine in cui è richiesta l'autenticazione.

### Aggiungere i cookie per le pagine che richiedono autenticazione ###

Per rendere più efficace la lettura di pagine per cui è richiesta l'autenticazione (per alcune pagine che utilizzano un speciale tipo di cookie) è necessario inserire manualmente un elenco di cookie piu completo che si può ottenere utilizzando l'estensione per Google Chrome _cookie.txt export (MOD)_ che ho creato.

Per utilizzarla basta scaricare [il file dell'estensione](https://code.google.com/p/vlc-shares/downloads/detail?name=cookie.txt%20export%20%28MOD%29.crx) da questo sito ed installarla.

Una volta installata, ripetere la procedura di aggiunta del bookmark e una volta giunti alla schermata di riepilogo delle informazioni incollare il testo ottenuto cliccando sull icona dell'estensione `cookie.txt export (MOD)`