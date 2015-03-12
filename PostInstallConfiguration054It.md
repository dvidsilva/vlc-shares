# Introduzione #

Questa guida permette di completare l'installazione di VLCShares ed eseguire la configurazione preliminare consigliata.



# Operazioni necessarie #

## Preparazione del primo avvio ##

Subito dopo l'installazione, dobbiamo procedere alla procedura di preparazione del primo avvio.

Aprite il vostro browser (VLCShares è compatibile con `Chrome 6+`, `Firefox 2+` e `Internet Explorer 9+`) all'indirizzo **http://localhost/vlc-shares**

Vi verrà presentata un'interfaccia come quella nell'immagine sotto:

[![](https://lh6.googleusercontent.com/_U6HIkh_ODAo/Ta6oEqfFFYI/AAAAAAAAAIU/47FBy46Gh-E/s400/installation.png)](https://picasaweb.google.com/lh/photo/yR7FDSu1uLAYcUYZDCT_dZQcgAM_hV8aasQj0QleA50?feat=directlink)

Compilate i campi:

  * scegliendo la lingua preferita fra quelle in lista
  * indicando se attivare o meno le funzionalità di autenticazione _(se non è prettamente necessario è consigliabile lasciarle disabilitate)_
  * specificando username e password per l'utente predefinito _(anche se l'autenticazione è disabilitata, è necessario impostare l'utente predefinito)_
  * scegliendo se e quali plugin aggiuntivi si vogliono automaticamente installare durante il primo avvio _(sarà possibile installarne altri tramite il Plugin Installer in una fase successiva)_. Questa funzionalità richiede un collegamento ad internet attivo.

Cliccate sul pulsante per continuare l'installazione. Potrebbe essere necessario qualche secondo se sono stati selezionati alcuni plugin opzionali (verranno scaricati da questo sito e installati automaticamente). Non abbiate fretta.

Al termine vi verrà presentata la pagina delle configurazioni.

## Configurazione dei percorsi ##

Procediamo alla configurazione dei percorsi ai file eseguibili di VLC, FFMPEG, SOPCAST e RTMPDUMP che abbiamo installato nelle fasi precedenti.

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta6tfK0iwyI/AAAAAAAAAIg/HTY0Qg5l8Wc/s400/configs_basic.png)](https://picasaweb.google.com/lh/photo/qY6XTT8zXjW7gUQA6w9yJ5QcgAM_hV8aasQj0QleA50?feat=directlink)

**Nota**: _se siete utenti Ubuntu ed avete seguito la guida all'installazione presente su questo sito, potete anche saltare queste fasi in quanto i percorsi predefiniti sono gia validi. Non c'è da cambiare altro._



### Percorso di VLC ###

Cliccate sul pulsante `Browse` alla destra di `Percorso VLC`. Navigate all'interno della struttura delle vostre directory fino a selezionare il file eseguibile di VLC.

**Attenzione**: dovete cliccare sul link "`[Seleziona]`" posto a destra della riga del file per poterlo selezionare.

Se siete utenti Windows, solitamente il percorso predefinito di VLC è:

```
C:\Program Files\VideoLan\Vlc\vlc.exe
```

In alternativa alla navigazione, potete anche scrivere direttamente il percorso all'interno del campo. In questo caso, assicuratevi di aggiungere anche il nome del file, non solo la directory che lo contiene.

Lo ripeto ancora una volta visto che sembra essere un errore comune: **assicuratevi di aver selezionato il file eseguibile corretto (e non soltanto il percorso alla directory)**

| Esempio **valido** | `C:\Program Files\VideoLan\Vlc\vlc.exe` |
|:-------------------|:----------------------------------------|
| Esempio **NON valido** | `C:\Program Files\VideoLan\Vlc\` |


### Percorso di FFMPEG ###

Cliccate sul pulsante `Browse` alla destra di `Percorso FFMPEG`. Navigate all'interno della struttura delle vostre directory fino a selezionare il file eseguibile di FFMPEG.

**Attenzione**: dovete cliccare sul link "`[Seleziona]`" posto a destra della riga del file per poterlo selezionare.

Se siete utenti Windows, solitamente il file `ffmpeg.exe` si trova all'interno della directory `\bin\`. Ad esempio:

```
C:\Program Files\FFMpeg\bin\ffmpeg.exe
```

In alternativa alla navigazione, potete anche scrivere direttamente il percorso all'interno del campo. In questo caso, assicuratevi di aggiungere anche il nome del file, non solo la directory che lo contiene.

Lo ripeto ancora una volta visto che sembra essere un errore comune: **assicuratevi di aver selezionato il file eseguibile corretto (e non soltanto il percorso alla directory)**

| Esempio **valido** | `C:\Program Files\FFMpeg\bin\ffmpeg.exe` |
|:-------------------|:-----------------------------------------|
| Esempio **NON valido** | `C:\Program Files\FFMpeg\bin\` |


### Percorso di RTMPDUMP ###

Cliccate sul pulsante `Browse` alla destra di `Percorso RTMPDump`. Navigate all'interno della struttura delle vostre directory fino a selezionare il file eseguibile `rtmpgw` di RTMPDump.

**Attenzione**: dovete cliccare sul link "`[Seleziona]`" posto a destra della riga del file per poterlo selezionare.

**Attenzione**: il file necessario a VLCShares è il file `rtmpgw` (`rtmpgw.exe` per gli utenti Windows) contenuto nell'archivio di RTMPDump

Ad esempio:

```
C:\Program Files\RTMPDump\rtmpgw.exe
```

In alternativa alla navigazione, potete anche scrivere direttamente il percorso all'interno del campo. In questo caso, assicuratevi di aggiungere anche il nome del file, non solo la directory che lo contiene.

Lo ripeto ancora una volta visto che sembra essere un errore comune: **assicuratevi di aver selezionato il file eseguibile corretto (e non soltanto il percorso alla directory)**

| Esempio **valido** | `C:\Program Files\RTMPDump\rtmpgw.exe` |
|:-------------------|:---------------------------------------|
| Esempio **NON valido** | `C:\Program Files\RTMPDump\` |


### Percorso di SOPCAST ###

Cliccate sul pulsante `Browse` alla destra di `Percorso SOPCAST`. Navigate all'interno della struttura delle vostre directory fino a selezionare il file eseguibile di SOPCAST.

**Attenzione**: dovete cliccare sul link "`[Seleziona]`" posto a destra della riga del file per poterlo selezionare.

Se siete utenti Windows, solitamente il percorso predefinito di SOPCAST è:

```
C:\Program Files\SopCast\SopCast.exe
```

Se siete utenti Ubuntu (o linux), solitamente il file eseguibile di SopCast è:

```
/usr/bin/sp-sc
```

In alternativa alla navigazione, potete anche scrivere direttamente il percorso all'interno del campo. In questo caso, assicuratevi di aggiungere anche il nome del file, non solo la directory che lo contiene.

Lo ripeto ancora una volta visto che sembra essere un errore comune: **assicuratevi di aver selezionato il file eseguibile corretto (e non soltanto il percorso alla directory)**

| Esempio **valido** | `C:\Program Files\SopCast\SopCast.exe` |
|:-------------------|:---------------------------------------|
| Esempio **NON valido** | `C:\Program Files\SopCast\` |


# Fasi opzionali #

## Installazione dei plugin aggiuntivi ##

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta63WoPs1PI/AAAAAAAAAIk/0ngjpldnX1A/s400/plugin_installer.png)](https://picasaweb.google.com/lh/photo/J3oN3UfaBs8IJwq-oSG4OJQcgAM_hV8aasQj0QleA50?feat=directlink)

### Installazione automatica ###

Dalla versione 0.5.4 è possibile installare i plugin ufficiali in maniera totalmente automatica. Per farlo basta aprire la pagina del `Plugin Installer` di VLCShares (disponibile all'indirizzo **http://localhost/vlc-shares/plugin**) e selezionare un plugin dalla lista cliccando sul pulsante `[+] Installa`.

### Installazione manuale ###

La vecchia modalità di installazione manuale è sempre supportata e non ha subito cambiamenti.

Per installare un nuovo plugin basta eseguire l'upload del file del plugin tramite il form disponibile sulla destra della pagina.

La lista dei plugin disponibili, con una breve descrizione, è disponibile [in questa pagina](PluginsList#Plugins_List.md).

## Autenticazione Utente ##

L'autenticazione utente è stata introdotta con VLCShares 0.5.4 al fine di aiutare giu utenti che intendono utilizzare VLCShares installandolo su una macchina condivisa o con accesso diretto dall'esterno.

Per tutti gli altri casi, si consiglia di utilizzare normalmente VLCShares con le funzionalità di autenticazione disattivate.

E' possibile attivare o disattivare l'autenticazione utente cliccando sul pulsante `Attivo/Disattivato` che è posto nella lista dei plugin sulla destra della pagina di configurazione (raggiungibile tramite **http://localhost/vlc-shares/configs**).

![https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta6tes_uhyI/AAAAAAAAAIY/TqQFxhH_FhQ/s800/configs_authoff.png](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta6tes_uhyI/AAAAAAAAAIY/TqQFxhH_FhQ/s800/configs_authoff.png)

L'autenticazione utente agisce in questo modo: per poter utilizzare tutti i servizi messi a disposizione da VLCShares l'utente deve identificarsi. Può farlo utilizzando Username e Password da un qualsiasi dispositivo con un web browser.

Per tutti gli altri dispositivi, come WIIMC ad esempio, è necessario utilizzare un URL speciale chiamato **URL Login Diretto**.

Ogni utente per il quale questa tipologia di autenticazione sia attiva, può conoscere il proprio `URL Login Diretto` andando nella pagina di gestione degli account utenti.

[![](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta8B6cEmE_I/AAAAAAAAAIo/t9GJzOHyg_A/s400/user_accounts.png)](https://picasaweb.google.com/lh/photo/a8eppb3amyNJ4e0weOeIRpQcgAM_hV8aasQj0QleA50?feat=directlink)

Per potersi autenticare, è sufficiente quindi collegarsi all'**URL Login Diretto**. VLCShares terrà traccia dell'IP e dello User-Agent utilizzato dal dispositivo e consentirà l'accesso per 24 ore dal momento dell'autenticazione. Oltre questo tempo limite sarà necessario eseguire una nuova autenticazione utilizzando lo stesso URL.

Nel caso in cui venga cambiata la password dell'account, verrà anche modificato l'`URL Login Diretto`.

**Attenzione**: la modalità di antenticazione tramite `URL Login Diretto` è potenzialmente poco sicura perchè consentirebbe l'accesso a VLCShares a tutti i dispositivi che condividono lo stesso IP e lo stesso User-Agent del dispositivo autenticato. E' quindi da considerarsi come un sistema estremo da utilizzare semplicemente nei casi in cui il normale tipo di autenticazione non possibile (ad esempio con WiiMC).

## Attivazione debug log ##

Questa procedura consente di attivare il debug log con il massimo livello di dettaglio.

Entrate nella pagina di configurazione di VLCShares (http://localhost/vlc-shares/configs)

Visualizzate le `Configurazioni Avanzate`.

Impostate:
  * **`Debug log attivo`** su **`Si`**
  * **`Livello debug`** su `*Tutto*`.

Cliccate su `Salva`.

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta6te03jLqI/AAAAAAAAAIc/XHQ6Q8nDjYg/s400/configs_debugon.png)](https://picasaweb.google.com/lh/photo/VyZXLyI-CU_00DEbT6o2IJQcgAM_hV8aasQj0QleA50?feat=directlink)
