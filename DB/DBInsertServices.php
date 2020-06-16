<?php

class DBInsertServices
{
    private $connection;
    private $tabelleDB = [ //Array delle tabelle
        "utente",
        "stats",
        "categoria",
        "sottocategoria",
        "domanda",
        "risposta",
        "sondaggio",
        "scelta",
        "chat",
        "messaggio",
        "valutazione",
        "votanti"
    ];
    private $campiTabelleDB = [ //Campi delle tabelle (array bidimensionale indicizzato con key)
        "utente" => [
            "email",
            "username",
            "password",
            "nome",
            "cognome",
            "bio",
            "attivo",
            "avatar"
        ],
        "stats" => [
            "cod_utente",
            "cod_categoria",
            "sommatoria_like",
            "sommatoria_dislike",
            "risposte_valutate"
        ],
        "categoria" => [
            "codice_categoria",
            "titolo"
        ],
        "sottocategoria" => [
            "codice_sottocategoria",
            "cod_categoria",
            "titolo"
        ],
        "domanda" => [
            "codice_domanda",
            "dataeora",
            "timer",
            "titolo",
            "descrizione",
            "cod_utente",
            "cod_categoria",
            "cod_preferita"
        ],
        "risposta" => [
            "codice_risposta",
            "descrizione",
            "num_like",
            "num_dislike",
            "cod_utente",
            "cod_domanda"
        ],
        "sondaggio" => [
            "codice_sondaggio",
            "dataeora",
            "titolo",
            "timer",
            "cod_utente",
            "cod_categoria"
        ],
        "scelta" => [
            "codice_scelta",
            "descrizione",
            "num_favorevoli",
            "cod_sondaggio"
        ],
        "chat" => [
            "codice_chat",
            "cod_utente0",
            "cod_utente1"
        ],
        "messaggio" => [
            "codice_messaggio",
            "dataeora",
            "testo",
            "visualizzato",
            "cod_chat",
            "msg_utente_id"
        ],
        "valutazione" => [
            "codice_valutazione",
            "cod_risposta",
            "cod_utente",
            "tipo_like"
        ],
        "votanti" => [
            "cod_scelta",
            "cod_utente",
            "cod_sondaggio"
        ]
    ];

    //Costruttore
    public function __construct()
    {
        //Setup della connessione con il DB
        $db = new DBConnectionManager();
        $this->connection = $db->runConnection();
    }

    //---- METODI PER GESTIRE LE QUERY ----
    //Visto che non è ancora presente in DB, la si crea
    public function insertScelte($descrizione, $cod_sondaggio)
    {
        $scelteTab = $this->tabelleDB[7];
        $campi = $this->campiTabelleDB[$scelteTab];
        //QUERY: INSERT INTO `scelta` () VALUES ();
        $query = (
            "INSERT INTO" . " " .
            $scelteTab . " (" .
            $campi[1] . ", " .
            $campi[3] . ") " .
            "VALUES " . "( " .
            "? , " .
            "? )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $descrizione, $cod_sondaggio);
        return $stmt->execute();
    }

    //Visto che non è ancora presente in DB, la si crea
    public function insertStats($id_utente, $id_categoria, $nLike, $nDislike, $n_ris)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: INSERT INTO `stats` (`cod_utente`, `cod_categoria`, `sommatoria_valutazioni`, `numero_valutazioni`) VALUES ('$id_utente', '$id_categoria', '$valutazione', '1');
        $query = (
            "INSERT INTO" . " " .
            $statsTab . " (" .
            $campi[0] . ", " .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ") " .
            "VALUES " . "( " .
            "? , " .
            "? , " .
            "? , " .
            "? , " .
            "? )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("siiii", $id_utente, $id_categoria, $nLike, $nDislike, $n_ris);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }

    public function inserisciRisposta($descrizione, $cod_utente, $cod_domanda)
    {
        $RispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$RispostaTab];
        //QUERY: //INSERT INTO risposta (descrizione, cod_utente, cod_domanda)
        ////VALUES ($descrizione, $cod_utente, $cod_domanda);

        $query = (
            "INSERT INTO" . " " .
            $RispostaTab . " ( " .
            $campi[1] . " , " .
            $campi[4] . " , " .
            $campi[5] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ? ) "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $descrizione, $cod_utente, $cod_domanda);
        return $stmt->execute();
    }

    public function inserisciValutazione($cod_risposta, $cod_utente, $tipo_like)
    {
        $ValutazioneTab = $this->tabelleDB[10];
        $campi = $this->campiTabelleDB[$ValutazioneTab];
        //QUERY: //INSERT INTO valutazione (codice_risposta, codice_utente, tipo_like)
        ////VALUES ($codice_risposta, $codice_utente, $tipo_like);

        $query = (
            "INSERT INTO" . " " .
            $ValutazioneTab . " ( " .
            $campi[1] . " , " .
            $campi[2] . " , " .
            $campi[3] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ? ) "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("isi", $cod_risposta, $cod_utente, $tipo_like);
        return $stmt->execute();
    }

    //Inserisci domanda
    public function inserisciDomanda($timer, $titolo, $descrizione, $cod_utente, $cod_categoria)
    {
        $DomandaTab = $this->tabelleDB[4];
        $campiDomanda = $this->campiTabelleDB[$DomandaTab];

        $query = (
            "INSERT INTO" . " " .
            $DomandaTab . " ( " .


            $campiDomanda[2] . " , " .
            $campiDomanda[3] . " , " .
            $campiDomanda[4] . " , " .
            $campiDomanda[5] . " , " .
            $campiDomanda[6] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ? , ? , ?  ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssi", $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
        return $stmt->execute();
    }


    public function inserisciSondaggio($dataeora, $titolo, $timer, $cod_utente, $cod_categoria)
    {
        $SondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$SondaggioTab];

        //QUERY: INSERT INTO sondaggio( dataeora, timer, titolo, cod_utente, cod_categoria) VALUES($dataeora, $titolo, $timer, $cod_utente, $cod_categoria)

        $query = (
            "INSERT INTO" . " " .
            $SondaggioTab . " ( " .
            $campiSondaggio[1] . " , " .
            $campiSondaggio[2] . " , " .
            $campiSondaggio[3] . " , " .
            $campiSondaggio[4] . " , " .
            $campiSondaggio[5] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ? , ? ,? ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssi", $dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
        return $stmt->execute();
    }

    //Crea Chat
    public function creaChat($cod_utente0, $cod_utente1)
    {
        $chatTab = $this->tabelleDB[8];
        $campiChat = $this->campiTabelleDB[$chatTab];


        //QUERY:INSERT into chat(FK_utente0, FK_utente1)
        //VALUES($utente0, $utente1)

        $query = (
            "INSERT INTO" . " " .
            $chatTab . " ( " .
            $campiChat[1] . " , " .
            $campiChat[2] . " ) " .
            "VALUES" . " ( " .
            " ? , ? ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $cod_utente0, $cod_utente1);
        return $stmt->execute();
    }

    //Inserisci messaggio
    public function inserisciMessaggio($testo, $visualizzato, $cod_chat, $msg_utente_id)
    {

        $messaggioTab = $this->tabelleDB[9];
        $campiMessaggio = $this->campiTabelleDB[$messaggioTab];

        $query = (
            "INSERT INTO" . " " .
            $messaggioTab . " ( " .
            $campiMessaggio[2] . " , " .
            $campiMessaggio[3] . " , " .
            $campiMessaggio[4] . " , " .
            $campiMessaggio[5] . " ) " .

            "VALUES" . " ( " .
            " ? , ?, ?, ? ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("siis", $testo, $visualizzato, $cod_chat, $msg_utente_id);
        return $stmt->execute();
    }

    public function inviaMessaggio($testo, $cod_utente0, $cod_utente1, $dataeora, $visualizzato)
    {

        $cod_chat = $this->trovaCodChat($cod_utente0, $cod_utente1);

        if (!$cod_chat) {                         //se la query non rest ituisce risultato, creo una nuova chate inserisco il nuovo messaggio
            $this->creaChat($cod_utente0, $cod_utente1);
            $cod_chat = $this->trovaCodChat($cod_utente0, $cod_utente1);

        }

        $this->inserisciMessaggio($dataeora, $testo, $visualizzato, $cod_chat);
    }

    public function votaSondaggio($codice_scelta, $cod_sondaggio)
    {
        $sceltaTab = $this->tabelleDB[7];
        $campiScelta = $this->campiTabelleDB[$sceltaTab];

        $query = (
            "UPDATE " .
            $sceltaTab . " " .
            "SET " .
            $campiScelta[2] . " = " . $campiScelta[2] . " +1 " .
            "WHERE " .
            $campiScelta[0] . "= ? " . " " .
            "AND " .
            $campiScelta[3] . "= ?  "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $codice_scelta, $cod_sondaggio);

        $result = $stmt->execute();
        return $result;
    }

    public function scegliRispostaPreferita($codice_domanda, $cod_preferita)
    {

        $tabella = $this->tabelleDB[4];

        $campi = $this->campiTabelleDB[$tabella];

        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[7] . " = ? " .
            " WHERE " .
            $campi[0] . " = ? "
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $cod_preferita, $codice_domanda);
        return $stmt->execute();
    }


    public function inserisciNuovoVotante($cod_scelta, $cod_utente, $cod_sondaggio)
    {
        $votantiTab = $this->tabelleDB[11];
        $campiVotanti = $this->campiTabelleDB[$votantiTab];
        //QUERY: INSERT INTO `scelta` () VALUES ();
        $query = (
            "INSERT INTO" . " " .
            $votantiTab . " (" .
            $campiVotanti[0] . ", " .
            $campiVotanti[1] . ", " .
            $campiVotanti[2] . ") " .
            "VALUES " . "( " .
            "? , " .
            "? , " .
            "? )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("isi", $cod_scelta, $cod_utente, $cod_sondaggio);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }

}