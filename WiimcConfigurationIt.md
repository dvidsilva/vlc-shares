# Introduzione #

Questo tutorial mostra come configurare WiiMC per la connessione a VLCShares



# Note #

In questo tutorial utilizzerò questi segnaposti:
  1. IP\_ADDRESS: per rappresentare l'indirizzo IP del server dove è installato VLCShares

# Versione 0.5.4 #

## ...senza Autenticazione Utente ##

Apri il file **`onlinemedia.xml`** nella directory di WiiMC. Il contenuto dovrebbe essere simile (non necessariamente identico) a quello mostrato qui sotto:
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

Aggiungi la riga scritta qui sotto prima del tag `</file>` all'interno del file:

```
<link name="VLC-Shares Collections" addr="http://IP_ADDRESS/vlc-shares/" />
```

Il file dopo la modifica dovrebbe avere questo aspetto:
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

**Nota**: ricordati di sostituire il segnaposto `IP_ADDRESS` con l'indirizzo IP del server su cui è installato VLCShares altrimenti WiiMC non sarà in grado di eseguire il collegamento.

## ...con l'Autenticazione Utente attiva ##


Apri il tuo browser a vai nella Dashboard di VLCShares (**http://localhost/vlc-shares/** solitamente).

Clicca sul pulsante `Account Utente` nella barra superiore.

[![](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta8B6cEmE_I/AAAAAAAAAIo/t9GJzOHyg_A/s400/user_accounts.png)](https://picasaweb.google.com/lh/photo/a8eppb3amyNJ4e0weOeIRpQcgAM_hV8aasQj0QleA50?feat=directlink)

Seleziona l' `URL Login Diretto` relativo al tuo Username e copialo negli appunti.

Apri il file **`onlinemedia.xml`** nella directory di WiiMC e aggiungi la riga come quella qui sotto prima del tag `</file>`:

```
<link name="VLC-Shares Collections" addr="INCOLLA_QUI_IL_TUO_URL_LOGIN_DIRETTO" />
```

Ricorda di sostituire `%IP_ADDRESS%` all'interno dell'`URL Login Diretto` con l'indirizzo IP del server su cui hai installato VLCShares.

Dopo la modifica il file dovrebbe apparire come quello qui sotto:


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

Segui le istruzioni in lingua inglese (e comunque devi spiegarmi a cosa ti servono queste istruzioni visto che la versione 0.5 è mooooooolto vecchia...)