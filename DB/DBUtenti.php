<?php

class DBUtenti
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
        "messaggio"
    ];
    private $campiTabelleDB = [ //Campi delle tabelle (array bidimensionale indicizzato con key)
        "utente" => [
            "email",
            "username",
            "password",
            "nome",
            "cognome",
            "bio",
            "attivo"
        ],
        "stats" => [
            "cod_utente",
            "cod_categoria",
            "sommatoria_valutazioni",
            "numero_valutazioni"
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
            "cod_categoria"
        ],
        "risposta" => [
            "codice_risposta",
            "descrizione",
            "valutazione",
            "cod_utente",
            "cod_domanda"
        ],
        "sondaggio" => [
            "codice_sondaggio",
            "dataeora",
            "titolo",
            "cod_utente",
            "cod_categoria"
        ],
        "scelta" => [
            "codice_scelta",
            "descrizione",
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
            "cod_chat"
        ]
    ];

    //Costruttore
    public function __construct()
    {
        //Setup della connessione col DB
        $db = new DBConnectionManager();
        $this->connection = $db->runConnection();
    }

    //---- METODI PER GESTIRE LE QUERY ----

    //Funzione di controllo presenza email per recuperare la password
    public function recuperoPsw($email)
    {
        $utenteTab = $this->tabelleDB[0]; //Tabella per la query
        $campi = $this->campiTabelleDB[$utenteTab]; //Campi per la query
        //QUERY: "SELECT email FROM utente WHERE email = ?
        $query = (
            "SELECT " .
            $campi[0] . " " .
            "FROM " .
            $utenteTab . " " .
            "WHERE " .
            $campi[0] . " = ? "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        //Se ha trovato un match tra la mail inserita e la tab utente, restituisce una bool TRUE
        return $stmt->num_rows > 0;
    }

    //Una volta che l'utente mi ha confermato la mail inviata cambio la password
    public function recuperaPassword($email, $password)
    {
        $password = hash('sha256', $password);

        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY:  "UPDATE TABLE SET password = ? WHERE email = ?"
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
        //La funzione termina con l'esecuzione della query
        return $stmt->execute();
    }

    //Seleziono tutto il contenuto di una risposta secondo un determinato ID
    public function visualizzaRisposta($id_risposta)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];
        //QUERY: "SELECT * FROM `risposta` WHERE ID = 'value'"
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_risposta);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_risposta, $descrizione, $valutazione, $cod_utente, $cod_domanda);
            $risposte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_risposta;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $valutazione;
                $temp[$campi[3]] = $cod_utente;
                $temp[$campi[4]] = $cod_domanda;
                array_push($risposte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $risposte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Prendo la domanda alla quale una risposta fa riferimento
    public function aChiAppartieniRisposta($id_risposta)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];
        //QUERY: SELECT cod_domanda FROM `risposta` WHERE ID = 'value'
        $query = (
            "SELECT " .
            $campi[4] . " " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_risposta);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_domanda);
            $domanda = array();
            while ($stmt->fetch()) { //Vado a prendermi la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[4]] = $cod_domanda;
                array_push($domanda, $temp); //Inserisco l'array $temp all'ultimo posto
            }
            return $domanda;
        } else {
            return null;
        }
    }

    //Prendo la categoria della domanda
    public function aChiAppartieniDomanda($id_domanda)
    {
        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];
        //QUERY: SELECT 'cod_categoria' FROM `domanda` WHERE codice = 'value'
        $query = (
            "SELECT " .
            $campi[6] . " " .
            "FROM " .
            $domandaTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_domanda);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_categoria);
            $categorie = array();
            while ($stmt->fetch()) { //Vado a prendermi la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[6]] = $cod_categoria;
                array_push($categorie, $temp);
            }
            return $categorie;
        } else {
            return null;
        }
    }

    //Si seleziona la tabella delle statistiche di un utente relativa ad una categoria
    public function controlloStats($id_utente, $id_categoria)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: SELECT * FROM stats WHERE cod_utente = ? AND cod_categoria = ?
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $statsTab . " " .
            "WHERE " . " " .
            $campi[0] . " = ?" .
            " AND " .
            $campi[1] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $id_utente, $id_categoria);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_utente, $cod_categoria, $voti, $n_risposte);
            $statistiche = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $cod_utente;
                $temp[$campi[1]] = $cod_categoria;
                $temp[$campi[2]] = $voti;
                $temp[$campi[3]] = $n_risposte;
                array_push($statistiche, $temp);
            }
            return $statistiche;
        }
        else return null;
    }

    //Se la riga relativa alle statistiche utente per categoria è presente viene aggiornata
    public function aggiornaStats($id_utente, $id_categoria, $valutazione, $n_val)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: UPDATE `stats` SET `sommatoria_valutazioni` = '$valutazione', `numero_valutazioni` = '$n_val' WHERE `cod_utente` = '$id_utente' AND cod_categoria` = $id_categoria
        $query = (
            "UPDATE " .
            $statsTab .
            "SET " .
            $campi[2] . " = ?, " .
            $campi[3] . " = ? " .
            "WHERE " .
            $campi[0] . " = ? " .
            "AND " .
            $campi[1] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iiii", $valutazione, $n_val, $id_utente, $id_categoria);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }
    
    //Visto che non è ancora presente in DB, la si crea
    public function insertStats($id_utente, $id_categoria, $valutazione)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: INSERT INTO `stats` (`cod_utente`, `cod_categoria`, `sommatoria_valutazioni`, `numero_valutazioni`) VALUES ('$id_utente', '$id_categoria', '$valutazione', '1');
        $query = (
            "INSERT INTO " .
            $statsTab . " (" .
            $campi[0] . ", " .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ") " .
            "VALUES " . "(" .
            "? , " .
            "? , " .
            "? , " .
            "? )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iiii", $id_utente, $id_categoria, $valutazione, 1);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }

    //Visualizzo il profilo di un utente
    public function visualizzaProfilo($email)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: SELECT * FROM `utente` WHERE Email = 'value'
        $query = (
            "SELECT" .
            "*" .
            "FROM" .
            $utenteTab . " " .
            "WHERE" .
            $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();
        return $result;
    }

    //Modifica profilo utente
    public function modificaProfilo($username, $password, $nome, $cognome, $bio, $email)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: UPDATE `utente` SET `Username`=[value-1], `Password`=[value-2],`Nome`=[value-3],`Cognome`=[value-4],`Bio`=[value-5] WHERE Email = “email_utente_corrente”
        $query = (
            "UPDATE" .
            $utenteTab . " " .
            "SET" .
            $campi[1] . " = ?," .
            $campi[2] . " = ?," .
            $campi[3] . " = ?," .
            $campi[4] . " = ?," .
            $campi[5] . " = ?" .
            "WHERE" .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sssss", $username, $password, $nome, $cognome, $bio);
        $result = $stmt->execute();
        return $result;
    }

// Funzione registrazione
    public function registrazione($email, $username, $password, $nome, $cognome, $bio, $attivo)
    {
        $tabella = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$tabella];

        $attivo = 0;

        $query = (
            "INSERT INTO " .
            $tabella . " (" .
            $campi[0] . ", " .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ", " .
            $campi[5] . ", " .
            $campi[6] . ") " .              //mette in automatico attivo a 0

            "VALUES (?,?,?,?,?,?,?)"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssssi", $email, $username, $password, $nome, $cognome, $bio, $attivo);
        $result = ($stmt->execute());
        return $result;
    }

    //Visualizza sondaggio
    public function visualizzaSondaggio($codice_sondaggio)
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$sondaggioTab];
        //QUERY: SELECT * FROM `sondaggio` WHERE ID = 'value'
        $query = (
            "SELECT" .
            "*" .
            "FROM" .
            $sondaggioTab . " " .
            "WHERE" .
            $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_sondaggio);
        $result = $stmt->execute();
        return $result;
    }

    //Modifica votazione (Mariano Buttino)
    public function modificaVotazione ($codice_risposta, $valutazione) {

        $tabella = $this->tabelleDB[5];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE TABLE SET valutazione = ? WHERE codice_risposta = ?"
        $query = (
            "UPDATE" .
            $tabella . " " .
            "SET" .
            $campi[2] . " = ? " .
            "WHERE" .
            $campi[0] . " = ? "
        );
        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $codice_risposta, $valutazione);
        return $stmt->execute();
    }

    //Modifica risposta (Mariano Buttino)
    public function modificaRisposta ($codice_risposta, $descrizione) {

        $tabella = $this->tabelleDB[5];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE TABLE SET descrizione = ? WHERE codice_risposta = ?"
        $query = (
            "UPDATE" .
            $tabella . " " .
            "SET" .
            $campi[1] . " = ? " .
            "WHERE" .
            $campi[0] . " = ? "
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("is", $codice_risposta, $descrizione);
        return $stmt->execute();
    }

    //Visualizza sondaggio per categoria (Mariano Buttino)
    public function visualizzaSondaggioPerCategoria ($codice_categoria, $codice_utente) {

        $sondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$sondaggioTab];
        $categoriaTab = $this->tabelleDB[2];
        $campiCategoria = $this->campiTabelleDB[$categoriaTab];
        //query: SELECT sondaggio.cod_sondaggio, sondaggio.dataeora, sondaggio.titolo,
        // WHERE sondaggio.cod_categoria = " ? " AND sondaggio.cod_utente = " ? "
        $query = (
            "SELECT " .
            $sondaggioTab . "." . $campiSondaggio[0] . ", " .
            $sondaggioTab . "." . $campiSondaggio[1] . ", " .
            $sondaggioTab . "." . $campiSondaggio[2] . " " .
            "FROM " . $sondaggioTab . " " .
            "WHERE " . $sondaggioTab . "." . $campiSondaggio[3] . "= ?" . " AND " . $sondaggioTab . "." .$campiSondaggio[4] . "= ?"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $codice_categoria, $codice_utente);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo);
            $sondaggio = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
                $temp[$campiSondaggio[1]] = $dataeora;
                $temp[$campiSondaggio[2]] = $titolo;
                array_push($sondaggio, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $sondaggio; //ritorno array $sondaggio riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }


    public function login($username, $password)
    {
        $utenteTab = $this->tabelleDB[0];
        $campiLogin = $this->campiTabelleDB[$utenteTab];

        $query = (
            "SELECT " .
            $campiLogin[0] . ", " .
            $campiLogin[1] . " " .

            "FROM " .
            $utenteTab . " " .
            "WHERE " .
            $campiLogin[1] . " = ? AND " .
            $campiLogin[2] . " = ? "
        );


        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $username);

            $utente = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiLogin[0]] = $email;
                $temp[$campiLogin[1]] = $username;
                array_push($utente, $temp);
            }

            return $utente;

        } else {
            return null;
        }
    }



    public function VisualizzaMessaggio($cod_chat)
    {
        $messaggioTab = $this->tabelleDB[8];
        $campiMessaggio = $this->campiTabelleDB[$messaggioTab];

        $query = (
            "SELECT "
             . "*" .
            "FROM " .
            $messaggioTab . " " .
            "WHERE " .
            $campiMessaggio[4] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_chat);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_messaggio, $dataeora, $testo,$visualizzato,cod_chat);
            $messaggio = array();
            while ($stmt->fetch()) {
                $temp = array();

                $temp[$campiMessaggio[0]] = $codice_messaggio;
                $temp[$campiMessaggio[1]] = $dataeora;
                $temp[$campiMessaggio[2]] = $testo;
                $temp[$campiMessaggio[3]] = $visualizzato;
                $temp[$campiMessaggio[4]] = cod_chat;
                array_push($messaggio, $temp);
            }
            return $messaggio;
        } else {
            return null;
        }


}

}
?>