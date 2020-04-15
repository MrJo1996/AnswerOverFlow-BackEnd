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
            "timer",
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
    public function controlloEmail($email)
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
        $stmt->bind_param("si", $id_utente, $id_categoria);
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
        } else return null;
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
            " SET " .
            $campi[2] . " = ?, " .
            $campi[3] . " = ? " .
            "WHERE " .
            $campi[0] . " = ? " .
            "AND " .
            $campi[1] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iisi", $valutazione, $n_val, $id_utente, $id_categoria);
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
            "VALUES " . "( " .
            "? , " .
            "? , " .
            "? , " .
            "1 )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sii", $id_utente, $id_categoria, $valutazione);
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
    }

    //Visualizzo il profilo di un utente
    public function visualizzaProfilo($email)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: SELECT email, username, nome, cognome, bio FROM `utente` WHERE Email = 'value'
        $query = (
            "SELECT " .
            $utenteTab . "." . $campi[0] . "," .
            $utenteTab . "." . $campi[1] . "," .
            $utenteTab . "." . $campi[3] . "," .
            $utenteTab . "." . $campi[4] . "," .
            $utenteTab . "." . $campi[5] . " " .
            "FROM " .
            $utenteTab . " " .
            "WHERE " .
            $utenteTab . "." . $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $username, $nome, $cognome, $bio);
            $user = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $email;
                $temp[$campi[1]] = $username;
                $temp[$campi[3]] = $nome;
                $temp[$campi[4]] = $cognome;
                $temp[$campi[5]] = $bio;
                array_push($user, $temp);
            }
            return $user;
        } else {
            return null;
        }
    }

    //Modifica profilo utente
    public function modificaProfilo($username, $password, $nome, $cognome, $bio, $email)
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
            $utenteTab . "." . $campi[5] . "= ? " .
            "WHERE " .
            $utenteTab . "." . $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssss", $username, $password, $nome, $cognome, $bio, $email);
        $result = $stmt->execute();
        return $result;
    }

    //Registrazione
    public function registrazione($email, $username, $password, $nome, $cognome, $bio)
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

    //Visualizza Sondaggio
    public function visualizzaSondaggio($codice_sondaggio)
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$sondaggioTab];

        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $sondaggioTab . " " .
            "WHERE " .
            $campiSondaggio[0] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_sondaggio);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer, $codice_utente, $codice_categoria);
            $sondaggio = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
                $temp[$campiSondaggio[1]] = $dataeora;
                $temp[$campiSondaggio[2]] = $titolo;
                $temp[$campiSondaggio[3]] = $timer;
                $temp[$campiSondaggio[4]] = $codice_utente;
                $temp[$campiSondaggio[5]] = $codice_categoria;
                array_push($sondaggio, $temp);
            }
            return $sondaggio;
        } else {
            return null;
        }
    }

    //Modifica valutazione per id risposta :query numero 5
    public function modificaValutazione($codice_risposta, $valutazione)
    {
        $tabella = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE risposta SET valutazione = ? WHERE  codice_risposta = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[2] . "= ? " .
            "WHERE " .
            $campi[0] . "= ?"
        );

        echo "QUERY: " . $query;

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $valutazione, $codice_risposta);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }

    //Modifica risposta (Mariano Buttino) :query numero 8
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

//Modifica domanda num10 PARTE 2
    public function modificaDomanda($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria)
    {

        $tabella = $this->tabelleDB[4];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  UPDATE Domanda
        //                        SET titolo=$titolo_inserito,dataeora=$valore,timer=$valore,  descrizione = $descrizione_inserita,           cod_categoria=$valore.
        //
        //                        WHERE = $Id_domanda_selezionata
        $query = (
            "UPDATE" .
            $tabella . " " .
            "SET" .
            $campi[1] . " = ? " .
            $campi[2] . " = ? " .
            $campi[3] . " = ? " .
            $campi[4] . " = ? " .
            $campi[6] . " = ? " .
            "WHERE" .
            $campi[0] . " = ? "
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("isissi", $codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria);
        return $stmt->execute();
    }

    //Visualizza sondaggio per categoria (Mariano Buttino) :non in elenco
    public function visualizzaSondaggioPerCategoria($cod_categoria)
    {

        $sondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$sondaggioTab];
        //query: SELECT sondaggio.cod_sondaggio, sondaggio.dataeora, sondaggio.titolo, sondaggio.timer,
        // WHERE sondaggio.cod_categoria = " ? "
        $query = (
            "SELECT " .
            $sondaggioTab . "." . $campiSondaggio[0] . ", " .
            $sondaggioTab . "." . $campiSondaggio[1] . ", " .
            $sondaggioTab . "." . $campiSondaggio[2] . ", " .
            $sondaggioTab . "." . $campiSondaggio[3] . " " .
            "FROM " . $sondaggioTab . " " .
            "WHERE " . $sondaggioTab . "." . $campiSondaggio[5] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_categoria);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer);
            $sondaggio = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
                $temp[$campiSondaggio[1]] = $dataeora;
                $temp[$campiSondaggio[2]] = $titolo;
                $temp[$campiSondaggio[3]] = $timer;
                array_push($sondaggio, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $sondaggio;//ritorno array $sondaggio riempito con i risultati della query effettuata.
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


    public function visualizzaMessaggi($cod_chat)
    {
        $messaggioTab = $this->tabelleDB[9];
        $campiMessaggio = $this->campiTabelleDB[$messaggioTab];

        $query = (
            "SELECT "
            . "* " .
            "FROM " .
            $messaggioTab . " " .
            " WHERE " .
            $campiMessaggio[4] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_chat);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_messaggio, $dataeora, $testo, $visualizzato, $cod_chat);
            $messaggio = array();
            while ($stmt->fetch()) {
                $temp = array();

                $temp[$campiMessaggio[0]] = $codice_messaggio;
                $temp[$campiMessaggio[1]] = $dataeora;
                $temp[$campiMessaggio[2]] = $testo;
                $temp[$campiMessaggio[3]] = $visualizzato;
                $temp[$campiMessaggio[4]] = $cod_chat;
                array_push($messaggio, $temp);
            }
            return $messaggio;
        } else {
            return null;
        }


    }


//Ricerca domanda aperta num11 PARTE 1

    public function ricercaDomandaAperta($categoria, $titoloDomanda)
    {
        $domandaTab = $this->tabelleDB[4];
        $campiDomanda = $this->campiTabelleDB[$domandaTab];


        $query = //QUERY: SELECT * FROM domanda WHERE timer > 0  AND categoria = $value OR titolo LIKE %$value%
            "SELECT " .
            $campiDomanda[0] . ", " .
            $campiDomanda[1] . " " .
            $campiDomanda[2] . ", " .
            $campiDomanda[3] . " " .
            $campiDomanda[4] . ", " .
            $campiDomanda[5] . " " .
            $campiDomanda[6] . ", " .

            "FROM " .
            $domandaTab . " " .
            "WHERE" .
            $campiDomanda[2] > 0 .
            "AND" . "(" . $campiDomanda[6] = " = ? " . "OR" . titolo . "LIKE" % " = ? " % ")";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->bind_param("ss", $categoria, $titoloDomanda);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
            $domandaAperta = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiDomanda[0]] = $codice_domanda;
                $temp[$campiDomanda[1]] = $dataeora;
                $temp[$campiDomanda[2]] = $timer;
                $temp[$campiDomanda[3]] = $titolo;
                $temp[$campiDomanda[4]] = $descrizione;
                $temp[$campiDomanda[5]] = $cod_utente;
                $temp[$campiDomanda[6]] = $cod_categoria;
                array_push($domandaAperta, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $domandaAperta
            }
            return $domandaAperta; //ritorno array $domandaAperta riempito con i risultati della query effettuata.
        } else {
            return null;

        }


    }

    //Ricerca sondaggio aperto

    public function ricercaSondaggioAperto($categoria, $titoloSondaggio)
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campiDomanda = $this->campiTabelleDB[$sondaggioTab];

        $query = //QUERY: SELECT * FROM sondaggio WHERE timer > 0 AND categoria = $categoria OR titolo LIKE %$titoloSondaggio%

            "SELECT " .
            $campiDomanda[0] . ", " .
            $campiDomanda[1] . " " .
            $campiDomanda[2] . ", " .
            $campiDomanda[3] . " " .
            "FROM " .
            $sondaggioTab . " " .
            "WHERE" .
            $campiDomanda[2] > 0 .
            "AND" . "(" . $campiDomanda[6] . " = ? " . "OR" . $titoloSondaggio . "LIKE" % " = ? " % ")";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->bind_param("ss", $codice_sondaggio, $dataeora, $cod_utente, $cod_categoria);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo);
            $sondaggioAperto = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiDomanda[0]] = $codice_sondaggio;
                $temp[$campiDomanda[1]] = $dataeora;
                $temp[$campiDomanda[2]] = $cod_utente;
                $temp[$campiDomanda[3]] = $cod_categoria;
                array_push($sondaggioAperto, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggioAperto
            }
            return $sondaggioAperto; //ritorno array $sondaggioAperto riempito con i risultati della query effettuata.
        } else {
            return null;

        }
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

//Cancella domanda num9 PARTE 2

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

        echo $query . "CIAOOOOOOOO";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_domanda_selezionata);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //inserisci votazione num6
    public function inserisciVotazione($valutazione)
    {
        $RispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$RispostaTab];
        //QUERY: //INSERT INTO  risposta (valutazione)
        ////VALUES ($valore_valutazione);
        ////WHERE   ID = $Id_risposta_selezionata
        $query = (
            "INSERT INTO " .
            $RispostaTab . " (" .
            $campi[2] . ") " .
            "VALUES " . "(" .
            "? )"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $valutazione);
        return $stmt->execute();
    }

    public function modificaSondaggio($codice_sondaggio, $dataeora, $titolo, $cod_categoria)
    {
        $Sondaggiotabella = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$Sondaggiotabella];
        //query:  UPDATE sondaggio
        //SET  DataeOra=$valore Titolo=$titolo_inserito cod_utente=$valore cod_categoria=$valore
        //WHERE codice_sondaggio=$valore
        $query = (
            "UPDATE " .
            $Sondaggiotabella . " " .
            "SET " .
            $Sondaggiotabella . "." . $campi[1] . "= ?," .
            $Sondaggiotabella . "." . $campi[2] . "= ?," .
            $Sondaggiotabella . "." . $campi[4] . "= ?" .
            "WHERE " .
            $Sondaggiotabella . "." . $campi[0] . "= ?"
        );
        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iisi", $codice_sondaggio, $dataeora, $titolo, $cod_categoria);
        $result = $stmt->execute();
        return $result;
    }

    //Visualizzo una domanda tramite il suo codice(ID)
    public function visualizzaDomanda($id_domanda)
    {

        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];

        //Query = select *from 'domanda' where id_domanda = 'value'

        $query = (
            "SELECT " .
            "* " .
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
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
            $domande = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[0]] = $codice_domanda;
                $temp[$campi[1]] = $dataeora;
                $temp[$campi[2]] = $timer;
                $temp[$campi[3]] = $titolo;
                $temp[$campi[4]] = $descrizione;
                $temp[$campi[5]] = $cod_utente;
                $temp[$campi[6]] = $cod_categoria;
                array_push($domande, $temp);

            }
            return $domande;
        } else {
            return null;
        }

    }


    public function eliminaProfilo($email)
    {

        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];

        // query = delete from 'utente' where e-mail= 'utente_selezionato' .00

        $query = (
            "DELETE FROM " .
            $utenteTab . " WHERE " .
            $campi[0] . " = ? "
        );

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //Cancella risposta
    /*public function cancellaRisposta($id_risposta_selezionata)
    {
        $tabella = $this->tabelleDB[5]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "  DELETE * FROM Risposta where ID = $Id_risposta_selezionata"


        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );


        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_risposta_selezionata);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }*/

    // Rierca domanda NON aperta
    public function ricercaDomanda($categoria/*, $titoloDomanda*/)
    {
        $domandaTab = $this->tabelleDB[4];
        $campiDomanda = $this->campiTabelleDB[$domandaTab];
        /*"codice_domanda",
                    "dataeora",
                    "timer",
                    "titolo",
                    "descrizione",
                    "cod_utente",
                    "cod_categoria"*/
        //QUERY: SELECT * FROM domanda WHERE categoria = $value OR titolo LIKE %$value%
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $domandaTab . " " .
            "WHERE" .
            //  "(" . $campiDomanda[6] . " = ? " . "OR" . $campiDomanda[3] . "LIKE " . "%" . " = ? " . "%" . ")");
            $campiDomanda[6] . " = ? ");

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $categoria/*, $titoloDomanda*/);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
            $domanda = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiDomanda[0]] = $codice_domanda;
                $temp[$campiDomanda[1]] = $dataeora;
                $temp[$campiDomanda[2]] = $timer;
                $temp[$campiDomanda[3]] = $titolo;
                $temp[$campiDomanda[4]] = $descrizione;
                $temp[$campiDomanda[5]] = $cod_utente;
                $temp[$campiDomanda[6]] = $cod_categoria;
                array_push($domanda, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $domanda
            }
            return $domanda; //ritorno array $domanda riempito con i risultati della query effettuata.
        } else {
            return null;

        }
    }

    // Rierca sondaggio NON aperto
    public function ricercaSondaggio($categoria, $titoloSondaggio)
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$sondaggioTab];

        //QUERY: SELECT * FROM sondaggio WHERE categoria = $value OR titolo LIKE %$value%
        $query = (
            "SELECT " .
            $campiSondaggio[0] . ", " .
            $campiSondaggio[1] . " " .
            $campiSondaggio[2] . ", " .
            $campiSondaggio[3] . " " .
            $campiSondaggio[4] . ", " .

            "FROM " .
            $sondaggioTab . " " .
            "WHERE" .
            "(" . $campiSondaggio[6] = " = ? " . "OR" . titolo . "LIKE" % " = ? " % ")");

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->bind_param("ss", $categoria, $titoloSondaggio);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $cod_utente, $cod_categoria);
            $sondaggio = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
                $temp[$campiSondaggio[1]] = $dataeora;
                $temp[$campiSondaggio[2]] = $titolo;
                $temp[$campiSondaggio[3]] = $cod_utente;
                $temp[$campiSondaggio[4]] = $cod_categoria;

                array_push($sondaggio, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $sondaggio; //ritorno array $sondaggio riempito con i risultati della query effettuata.
        } else {
            return null;

        }

    }

    //Inserisci domanda
    public function inserisciDomanda($dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria)
    {
        $DomandaTab = $this->tabelleDB[4];
        $campiDomanda = $this->campiTabelleDB[$DomandaTab];

        $query = (
            "INSERT INTO" . " " .
            $DomandaTab . " ( " .

            $campiDomanda[1] . " , " .
            $campiDomanda[2] . " , " .
            $campiDomanda[3] . " , " .
            $campiDomanda[4] . " , " .
            $campiDomanda[5] . " , " .
            $campiDomanda[6] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ? , ? , ? , ?  ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iisssi", $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
        return $stmt->execute();
    }

    //n.11(Team Cassetta) Inserisci sondaggio

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


    // trovaCodChat

    public function trovaCodChat($cod_utente0, $cod_utente1)
    {
        $chatTab = $this->tabelleDB[8];
        $campiChat = $this->campiTabelleDB[$chatTab];

        //QUERY:  SELECT id FROM chat WHERE FK_Utente0 = $cod_utente0 AND FK_Utente1=        $cod_utente1
        //               					OR           	        FK_Utente1 = $cod_utente0
        //                                                      AND FK_Utente0 = $cod_utente1
        $query = (
            "SELECT " .
            $campiChat[0] .
            "  " .
            "FROM " .
            $chatTab . "  " .
            "WHERE " .
            "  " .
            $campiChat[1] . " = ? " . "  " . " AND " . "  " . $campiChat[2] . " = ? ");

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $cod_utente0, $cod_utente1);
        $stmt->execute();
        $stmt->store_result();

        $cod_chat_array = array();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_chat);

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiChat[0]] = $codice_chat;
                array_push($cod_chat_array, $temp);
            }
        }

        return $cod_chat_array;
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
    public function inserisciMessaggio($dataeora, $testo, $visualizzato, $cod_chat)
    {

        $messaggioTab = $this->tabelleDB[9];
        $campiMessaggio = $this->campiTabelleDB[$messaggioTab];

        $query = (
            "INSERT INTO" . " " .
            $messaggioTab . " ( " .
            $campiMessaggio[1] . " , " .
            $campiMessaggio[2] . " , " .
            $campiMessaggio[3] . " , " .
            $campiMessaggio[4] . " ) " .
            "VALUES" . " ( " .
            " ? , ? , ?, ? ) "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssii", $dataeora, $testo, $visualizzato, $cod_chat);
        return $stmt->execute();
    }


    //n3(team cassetta)Invia Messaggio

    public function inviaMessaggio($testo, $cod_utente0, $cod_utente1, $dataeora, $visualizzato)
    {

        $cod_chat = $this->trovaCodChat($cod_utente0, $cod_utente1);

        if (!$cod_chat) {                         //se la query non rest ituisce risultato, creo una nuova chate inserisco il nuovo messaggio
            $this->creaChat($cod_utente0, $cod_utente1);
            $cod_chat =$this->trovaCodChat($cod_utente0, $cod_utente1);

        }

        $this->inserisciMessaggio($dataeora, $testo, $visualizzato, $cod_chat);
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


}

?>l