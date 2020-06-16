<?php

class DBUpdateServices
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

    //Se la riga relativa alle statistiche utente per categoria è presente viene aggiornata
    public function aggiornaStats($id_utente, $id_categoria, $nLike, $nDislike, $n_ris)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: UPDATE `stats` SET `sommatoria_valutazioni` = '$valutazione', `numero_valutazioni` = '$n_val' WHERE `cod_utente` = '$id_utente' AND cod_categoria` = $id_categoria
        $query = (
            "UPDATE " .
            $statsTab .
            " SET " .
            $campi[2] . " = ?, " .
            $campi[3] . " = ?, " .
            $campi[4] . " = ? " .
            "WHERE " .
            $campi[0] . " = ? " .
            "AND " .
            $campi[1] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iiisi", $nLike, $nDislike, $n_ris, $id_utente, $id_categoria);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }

    //Modifica profilo utente
    public function modificaProfilo($username, $password, $nome, $cognome, $bio, $email, $avatar)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: UPDATE `utente` SET `Username`=[value-1], `Password`=[value-2],`Nome`=[value-3],`Cognome`=[value-4],`Bio`=[value-5] WHERE Email = “email_utente_corrente”
        $query = (
            "UPDATE " .
            $utenteTab . " " .
            "SET " .
            $utenteTab . "." . $campi[1] . "= ?," .
            $utenteTab . "." . $campi[2] . "= ?," .
            $utenteTab . "." . $campi[3] . "= ?," .
            $utenteTab . "." . $campi[4] . "= ?," .
            $utenteTab . "." . $campi[5] . "= ?," .
            $utenteTab . "." . $campi[7] . "= ? " .
            "WHERE " .
            $utenteTab . "." . $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sssssss", $username, $password, $nome, $cognome, $bio, $avatar, $email);
        $result = $stmt->execute();
        return $result;
    }

    //Modifica profilo utente
    public function modificaParteProfilo($username, $nome, $cognome, $bio, $email, $avatar)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: UPDATE `utente` SET `Username`=[value-1], `Password`=[value-2],`Nome`=[value-3],`Cognome`=[value-4],`Bio`=[value-5] WHERE Email = “email_utente_corrente”
        $query = (
            "UPDATE " .
            $utenteTab . " " .
            "SET " .
            $utenteTab . "." . $campi[1] . "= ?," .
            $utenteTab . "." . $campi[3] . "= ?," .
            $utenteTab . "." . $campi[4] . "= ?," .
            $utenteTab . "." . $campi[5] . "= ?," .
            $utenteTab . "." . $campi[7] . "= ? " .
            "WHERE " .
            $utenteTab . "." . $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssss", $username, $nome, $cognome, $bio, $avatar, $email);
        $result = $stmt->execute();
        return $result;
    }

    //Modifica num_like per id risposta
    public function modificaNumLike($codice_risposta)
    {
        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE risposta SET num_like = ? WHERE  codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[2] . " = " . $campi[2] . " +1 " .
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

    //Modifica num_dislike per id risposta
    public function modificaNumDisLike($codice_risposta)
    {
        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE risposta SET num_dis_like = ? WHERE  codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[3] . " = " . $campi[3] . " +1 " .
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

    //Modifica tipo_like per id risposta e id utente
    public function modificaTipo_like($tipo_like, $cod_risposta, $cod_utente)
    {
        $tabella = $this->tabelleDB[10];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE valutazione SET tipo_like = ? WHERE  cod_risposta = ? AND cod_utente = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[2] . " = ? " .
            "WHERE " .
            $campi[0] . " = ? AND " . $campi[1] . " = ? "
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iis", $tipo_like, $cod_risposta, $cod_utente);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }

    //Modifica risposta
    public function modificaRisposta($codice_risposta, $descrizione)
    {

        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];

        //query:  "UPDATE TABLE SET descrizione = ? WHERE codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[1] . " = ? " .
            "WHERE " .
            $campi[0] . " = ? "
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $descrizione, $codice_risposta);
        $result = $stmt->execute();

        return $result;
    }

    public function modificaDomanda($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria, $cod_preferita)
    {

        $tabella = $this->tabelleDB[4];

        $campi = $this->campiTabelleDB[$tabella];

        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[1] . " = ? ," .
            $campi[2] . " = ? ," .
            $campi[3] . " = ? ," .
            $campi[4] . " = ? ," .
            $campi[6] . " = ? ," .
            $campi[7] . " = ? " .
            " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssiii", $dataeora, $timer, $titolo, $descrizione, $cod_categoria, $cod_preferita, $codice_domanda);
        return $stmt->execute();
    }

    public function modificaSondaggio($titolo, $timer, $codice_sondaggio)
    {
        $Sondaggiotabella = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$Sondaggiotabella];

        $query = (
            "UPDATE " .
            $Sondaggiotabella . " " .
            "SET " .
            $Sondaggiotabella . "." . $campi[2] . " = ?," .
            $Sondaggiotabella . "." . $campi[3] . " = ? " .
            "WHERE " .
            $Sondaggiotabella . "." . $campi[0] . " = ?"
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $titolo, $timer, $codice_sondaggio);
        $result = $stmt->execute();
        return $result;
    }

    public function modificaPasssword($password, $email)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];

        $query = (
            "UPDATE " .
            $utenteTab . " " .
            "SET " .
            $campi[2] . " = ? " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $password, $email);
        $result = $stmt->execute();
        return $result;
    }
}