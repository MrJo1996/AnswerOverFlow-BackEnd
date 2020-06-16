<?php

class DBDeleteServices
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
    // cancella valutazione per id riposta e id utente
    public function cancellaValutazione($cod_risposta, $cod_utente)
    {
        $tabella = $this->tabelleDB[10]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "  DELETE * FROM valutazione where cod_risposta = ? AND cod_utente = ?"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? AND " .
            $campi[1] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("is", $cod_risposta, $cod_utente);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    public function cancellaSondaggio($id_sondaggio_selezionato)
    {
        $tabella = $this->tabelleDB[6]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "  DELETE * FROM Sondaggi where ID = $Id_sondaggio_selezionato"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );


        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_sondaggio_selezionato);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    public function cancellaDomanda($id_domanda_selezionata)
    {
        $tabella = $this->tabelleDB[4]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "  DELETE * FROM domanda where ID = $Id_domanda_selezionata"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_domanda_selezionata);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //Rimuovi Risposta
    public function rimuoviRisposta($codice_risposta)
    {
        $tabella = $this->tabelleDB[5]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  " DELETE FROM risposta WHERE ID = $codice_risposta"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );

        if ($this->visualizzaRisposta($codice_risposta) == null) {
            ////Controllo se esiste, non restituiva error nel caso in cui si passava un codice non esistente nel db.
            //Potrebbe anche essere eliminato il controllo perchè dovrebbe essere impossibibile passare un codice non esistente dall' app.
            return null;
        } else {
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $codice_risposta);
            $result = $stmt->execute();
            $stmt->store_result();

            return $result;
        }
    }


//Rimuovi sondaggio per id
    public function rimuoviSondaggio($codice_sondaggio)
    {
        $tabella = $this->tabelleDB[6]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  " DELETE FROM sondaggio WHERE ID = $codice_risposta"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );

        if ($this->visualizzaSondaggio($codice_sondaggio) == null) {
            return null;
        } else {
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $codice_sondaggio);
            $result = $stmt->execute();
            $stmt->store_result();

            return $result;
        }
    }

    public function eliminaValutazione($codice_valutazione)
    {
        $valutazioneTab = $this->tabelleDB[10]; //Tabella per la query
        $campi = $this->campiTabelleDB[$valutazioneTab];
        //query:  "  DELETE * FROM Sondaggi where ID = $Id_sondaggio_selezionato"

        $query = (
            "DELETE FROM " .
            $valutazioneTab . " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_valutazione);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    public function togliLike($codice_risposta)
    {
        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE risposta SET num_like = ? WHERE  codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[2] . " = " . $campi[2] . " -1 " .
            "WHERE " .
            $campi[0] . "= ?"
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_risposta);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }


    public function togliDislike($codice_risposta)
    {
        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE risposta SET num_dis_like = ? WHERE  codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[3] . " = " . $campi[3] . " -1 " .
            "WHERE " .
            $campi[0] . "= ?"
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_risposta);

        $result = $stmt->execute();

        return $result;
    }

}