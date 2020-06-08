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

    public function ricercaScelteDelSondaggio($codice_sondaggio)
    {
        $sceltaTab = $this->tabelleDB[7]; //Tabella per la query
        $campi = $this->campiTabelleDB[$sceltaTab]; //Campi per la query
        $query = (
            "SELECT * " .
            "FROM " .
            $sceltaTab . " " .
            "WHERE " .
            $campi[3] . " = ? "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_sondaggio);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_scelta, $descrizione, $num_favorevoli, $cod_sondaggio);
            $scelte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_scelta;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $num_favorevoli;
                $temp[$campi[3]] = $cod_sondaggio;
                array_push($scelte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $scelte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

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
        //Termina con la bool true se la sessione è andata a buon fine
        return $stmt->execute();
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
    public function visualizzaRisposta($codice_risposta)
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
        $stmt->bind_param("i", $codice_risposta);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_risposta, $descrizione, $num_like, $num_dislike, $cod_utente, $cod_domanda);
            $risposte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_risposta;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $num_like;
                $temp[$campi[3]] = $num_dislike;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_domanda;
                array_push($risposte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $risposte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutto il contenuto di una risposta secondo un determinato ID
    public function risposte($codice_risposta)
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
            $campi[5] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_risposta);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_risposta, $descrizione, $num_like, $num_dislike, $cod_utente, $cod_domanda);
            $risposte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_risposta;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $num_like;
                $temp[$campi[3]] = $num_dislike;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_domanda;
                array_push($risposte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $risposte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutto il contenuto di una risposta secondo una determinata mail
    public function visualizzaRisposteUtente($email)
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
            $campi[4] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $descrizione, $num_like, $num_dislike, $cod_utente, $cod_domanda);
            $risposte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $email;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $num_like;
                $temp[$campi[3]] = $num_dislike;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_domanda;
                array_push($risposte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $risposte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutto il contenuto di una risposta secondo un determinato ID
    public function visualizzaChats($codice_utente)
    {
        $chatTab = $this->tabelleDB[8];
        $campi = $this->campiTabelleDB[$chatTab];
        //QUERY: SELECT * FROM chat WHERE (cod_utente0 = "gmailverificata" OR cod_utente1 = "gmailverificata")
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $chatTab . " " .
            "WHERE (" .
            $campi[1] . " = ? OR " .
            $campi[2] . " = ?)"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $codice_utente, $codice_utente);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_chat, $cod_utente0, $cod_utente1);
            $chats = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_chat;
                $temp[$campi[1]] = $cod_utente0;
                $temp[$campi[2]] = $cod_utente1;
                array_push($chats, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $chats; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutto il contenuto di una risposta secondo un determinato ID
    public function ricercaCategorie()
    {
        $categoriaTab = $this->tabelleDB[2];
        $campi = $this->campiTabelleDB[$categoriaTab];
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $categoriaTab
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_categoria, $titolo);
            $categorie = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_categoria;
                $temp[$campi[1]] = $titolo;
                array_push($categorie, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $categorie; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutte le risposte valutate secondo una categoria e una mail
    public function selezionaRisposteValutate($email, $cod_categoria)
    {
        $domandaTab = $this->tabelleDB[4];
        $rispostaTab = $this->tabelleDB[5];
        $campiDomanda = $this->campiTabelleDB[$domandaTab];
        $campiRisposta = $this->campiTabelleDB[$rispostaTab];
        //QUERY:
        //SELECT codice_risposta
        //FROM risposta LEFT JOIN domanda
        //ON risposta.cod_domanda = domanda.codice_domanda
        //WHERE (num_like > 0 OR num_dislike > 0)
        // AND risposta.cod_utente = "pippo.cocainasd.com"
        // AND domanda.cod_categoria = "1"
        $query = (
            "SELECT " . $campiRisposta[0] .
            " FROM " . $rispostaTab . " ris LEFT JOIN " . $domandaTab . " dom " .
            " ON ris." . $campiRisposta[5] . " = dom." . $campiDomanda[0] .
            " WHERE (" . $campiRisposta[2] . " > 0 OR " . $campiRisposta[3] . " > 0)" .
            " AND dom." . $campiDomanda[6] . " = ?" .
            " AND ris." . $campiRisposta[4] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("is", $cod_categoria, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($Nrisposte_valutate);
            $risposteTrovate = array();
            while ($stmt->fetch()) { //Vado a prendermi la risposta della query
                //Indicizzo con key i dati nell'array
                $temp = $Nrisposte_valutate;
                array_push($risposteTrovate, $temp); //Inserisco l'array $temp all'ultimo posto
            }
            return $risposteTrovate; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }

    //Seleziono tutto il contenuto di una risposta secondo una determinata mail
    public function contaValutazioni($cods)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campiRisposta = $this->campiTabelleDB[$rispostaTab];
        $in = str_repeat('?,', count($cods) - 1) . '?'; // placeholders
        //QUERY:
        //  SELECT SUM(num_like) as num_like,
        //	SUM(num_dislike) as num_dislike
        //	FROM risposta
        //	WHERE codice_risposta IN
        //	(valori della query precedente)
        $query = (
            " SELECT SUM(" . $campiRisposta[2] . ") as num_like," .
            " SUM(" . $campiRisposta[3] . ") as num_dislike" .
            " FROM " . $rispostaTab .
            " WHERE " . $campiRisposta[0] . " IN ($in)"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $types = str_repeat('i', count($cods));
//        echo $in . "\n";
//        echo $types . "\n";
//        foreach ($cods as $cod){
//            echo $cod . " ";
//        }
        $stmt->bind_param($types, ...$cods);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nLikes, $nDislikes);
            $valutazioniTrovate = array();
            if ($stmt->fetch()) { //Vado a prendermi la risposta della query=
                //Indicizzo con key i dati nell'array
                $valutazioniTrovate[$campiRisposta[2]] = $nLikes;
                $valutazioniTrovate[$campiRisposta[3]] = $nDislikes;
            }
            return $valutazioniTrovate; //ritorno array $risposte riempito con i risultati della query effettuata
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
            $campi[5] . " " .
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
                $temp[$campi[5]] = $cod_domanda;
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
    public function vcontrolloStats($id_utente, $id_categoria)
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
            $stmt->bind_result($cod_utente, $cod_categoria, $likes, $dislikes, $n_risposte);
            $statistiche = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $cod_utente;
                $temp[$campi[1]] = $cod_categoria;
                $temp[$campi[2]] = $likes;
                $temp[$campi[3]] = $dislikes;
                $temp[$campi[4]] = $n_risposte;
                array_push($statistiche, $temp);
            }
            return $statistiche;
        } else return null;
    }

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
            $utenteTab . "." . $campi[5] . "," .
            $utenteTab . "." . $campi[7] . " " .
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
            $stmt->bind_result($email, $username, $nome, $cognome, $bio, $avatar);
            $user = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $email;
                $temp[$campi[1]] = $username;
                $temp[$campi[3]] = $nome;
                $temp[$campi[4]] = $cognome;
                $temp[$campi[5]] = $bio;
                $temp[$campi[7]] = $avatar;
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
    public function registrazione($email, $username, $password, $nome, $cognome, $bio, $avatar)
    {
        $tabella = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$tabella];
        $attivo = 0;

        $query = (
            "INSERT INTO" . " " .
            $tabella . " (" .
            $campi[0] . ", " .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ", " .
            $campi[5] . ", " .
            $campi[6] . ", " .              //mette in automatico attivo a 0
            $campi[7] . ") " .

            "VALUES (?,?,?,?,?,?,?,?)"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssssis", $email, $username, $password, $nome, $cognome, $bio, $attivo, $avatar);
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

    //Visualizza tutti i sondaggi
    public function visualizzaSondaggi()
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$sondaggioTab];

        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $sondaggioTab
        );

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer, $codice_utente, $codice_categoria);
            $sondaggi = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
                $temp[$campiSondaggio[1]] = $dataeora;
                $temp[$campiSondaggio[2]] = $titolo;
                $temp[$campiSondaggio[3]] = $timer;
                $temp[$campiSondaggio[4]] = $codice_utente;
                $temp[$campiSondaggio[5]] = $codice_categoria;
                array_push($sondaggi, $temp);
            }
            return $sondaggi;
        } else {
            return null;
        }
    }

    //Visualizzo il numero di like di una risposta tramite il suo codice(ID)
    public function visualizzaNumLikeRisposta($codice_risposta)
    {

        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];

        //Query = select num_like from 'risposta' where codice_risposta = 'value'

        $query = (
            "SELECT " .
            " $campi[2] " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_risposta);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_like);
            $risposta = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[2]] = $num_like;
                array_push($risposta, $temp);

            }
            return $risposta;
        } else {
            return null;
        }

    }

    //Visualizzo il numero di dislike di una risposta tramite il suo codice(ID)
    public function visualizzaNumDislikeRisposta($codice_risposta)
    {

        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];

        //Query = select num_like from 'risposta' where codice_risposta = 'value'

        $query = (
            "SELECT " .
            " $campi[3] " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_risposta);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_dislike);
            $risposta = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[2]] = $num_dislike;
                array_push($risposta, $temp);

            }
            return $risposta;
        } else {
            return null;
        }

    }

    //Controllo se un utente ha gia valutato una risposta
    public function checkIfUserHasAlreadyEvaluatedResponse($cod_risposta)
    {

        $valutazioneTab = $this->tabelleDB[10];
        $campi = $this->campiTabelleDB[$valutazioneTab];

        //Query = select cod_utente from 'valutazione' where cod_risposta = 'value'

        $query = (
            "SELECT " .
            " $campi[1] " .
            "FROM " .
            $valutazioneTab . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_risposta);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_utente);
            $valutazione = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[1]] = $cod_utente;
                array_push($valutazione, $temp);

            }
            return $valutazione;
        } else {
            return null;
        }

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

        echo "QUERY: " . $query;

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

        echo "QUERY: " . $query;

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i",  $codice_risposta);

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

        echo "QUERY: " . $query;

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iis", $tipo_like, $cod_risposta, $cod_utente);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }

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

        // echo $query . "CIAOOOOOOOO";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("is", $cod_risposta, $cod_utente);
        $result = $stmt->execute();
        $stmt->store_result();

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
    public function modificaDomanda($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria, $cod_preferita)
    {

        $tabella = $this->tabelleDB[4];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  UPDATE Domanda
        //                        SET titolo=$titolo_inserito,dataeora=$valore,timer=$valore,  descrizione = $descrizione_inserita,     cod_categoria=$valore.
        //
        //                        WHERE = $Id_domanda_selezionata
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

        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssiii", $dataeora, $timer, $titolo, $descrizione, $cod_categoria, $cod_preferita, $codice_domanda);
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
        $stmt->bind_param("i", $cod_categoria);
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
            $campiLogin[1] . ", " .
            $campiLogin[3] . ", " .
            $campiLogin[4] . ", " .
            $campiLogin[7] . " " .

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
            $stmt->bind_result($email, $username, $nome, $cognome, $avatar);

            $utente = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiLogin[0]] = $email;
                $temp[$campiLogin[1]] = $username;
                $temp[$campiLogin[3]] = $nome;
                $temp[$campiLogin[4]] = $cognome;
                $temp[$campiLogin[7]] = $avatar;

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

    public function visualizzaLastMessaggio($cod_chat)
    {
        $messaggioTab = $this->tabelleDB[9];
        $campiMessaggio = $this->campiTabelleDB[$messaggioTab];

        $query = (//SELECT * FROM messaggio WHERE cod_chat=? ORDER BY codice_messaggio DESC LIMIT 1
            "SELECT "
            . "* " .
            "FROM " .
            $messaggioTab . " " .
            " WHERE " .
            $campiMessaggio[4] . " = ? " .
            "ORDER BY " . $campiMessaggio[0] . " DESC LIMIT 1"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_chat);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_messaggio, $dataeora, $testo, $visualizzato, $cod_chat, $idUtente);
            $messaggio = array();

            if ($stmt->fetch()) {
                $messaggio[$campiMessaggio[0]] = $codice_messaggio;
                $messaggio[$campiMessaggio[1]] = $dataeora;
                $messaggio[$campiMessaggio[2]] = $testo;
                $messaggio[$campiMessaggio[3]] = $visualizzato;
                $messaggio[$campiMessaggio[4]] = $cod_chat;
                $messaggio[$campiMessaggio[5]] = $idUtente;
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


        $query = (//QUERY: SELECT * FROM domanda WHERE timer > 0  AND categoria = $value OR titolo LIKE %$value%
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
            "AND" . "(" . $campiDomanda[6] = " = ? " . "OR" . $campiDomanda[3] . "LIKE" % " = ? " % ")"
        );

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

        // echo $query . "CIAOOOOOOOO";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_domanda_selezionata);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //inserisci valutazione num6
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

        echo $query;

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("isi", $cod_risposta, $cod_utente, $tipo_like);
        return $stmt->execute();
    }

    //inserisci valutazione num6
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

        echo $query;

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $descrizione, $cod_utente, $cod_domanda);
        return $stmt->execute();
    }

    public function modificaSondaggio($titolo, $timer, $codice_sondaggio)
    {
        $Sondaggiotabella = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$Sondaggiotabella];
        //query:  UPDATE sondaggio
        //SET   Titolo=$titolo_inserito Timer=$timer
        //WHERE codice_sondaggio=$valore
        $query = (
            "UPDATE " .
            $Sondaggiotabella . " " .
            "SET " .
            $Sondaggiotabella . "." . $campi[2] . " = ?," .
            $Sondaggiotabella . "." . $campi[3] . " = ? " .
            "WHERE " .
            $Sondaggiotabella . "." . $campi[0] . " = ?"
        );

        echo $query . "Query";
        //invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssi", $titolo, $timer, $codice_sondaggio);
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
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria, $cod_preferita);
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
                $temp[$campi[7]] = $cod_preferita;
                array_push($domande, $temp);

            }
            return $domande;
        } else {
            return null;
        }

    }

    public function visualizzaDomandeHome()
    {


        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];

        //Query = select *from 'domanda' where id_domanda = 'value'
        /*
         *
         *          codice_domanda",
                    "dataeora",
                    "timer",
                    "titolo",
                    "descrizione",
                    "cod_utente",
                    "cod_categoria",
                    "cod_preferita"*/
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $domandaTab . " " .
            "WHERE " .
            $campi[2] . " > '00:00:00' " .
            " " .
            "ORDER by " .
            $campi[1] .
            " " .
            "DESC"
        );

        $stmt = $this->connection->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria, $cod_preferita);
            $domande = array();
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
                $temp[$campi[7]] = $cod_preferita;
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
            "(" . $campiSondaggio[6] = " = ? " . "OR" . $campiSondaggio[2] . "LIKE" % " = ? " % ")");

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

    //Prende l'ultimo codice del sondaggio inserito dall'utente
    public function prendiCodiceSondaggio($cod_utente)
    {
        $SondaggioTab = $this->tabelleDB[6];
        $campiSondaggio = $this->campiTabelleDB[$SondaggioTab];

        //QUERY: SELECT codice_sondaggio
        //	    FROM sondaggio
        //	    WHERE cod_utente="gmailverificata"
        //	    ORDER BY codice_sondaggio DESC LIMIT 1

        $query = (
            "SELECT" . " " .
            $campiSondaggio[0] . " " .
            "FROM" . " " .
            $SondaggioTab . " " .
            "WHERE" . " " .
            $campiSondaggio[4] . " = ? " .
            "ORDER BY " . $campiSondaggio[0] . " DESC LIMIT 1"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio);

            if ($stmt->fetch()) {
                $temp[$campiSondaggio[0]] = $codice_sondaggio;
            }
            return $temp;
        } else return null;
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
            $cod_chat = $this->trovaCodChat($cod_utente0, $cod_utente1);

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


//Visualizza profilo utente
    public function ricercaProfiloPerUsername($username)
    {
        $utenteTab = $this->tabelleDB[0];
        $campiUtente = $this->campiTabelleDB[$utenteTab];

        $query = (
            "SELECT " .
            "  " .
            $campiUtente[0] .
            "  ," .
            $campiUtente[1] .
            "  ," .
            $campiUtente[3] .
            "  ," .
            $campiUtente[4] .
            "  ," .
            $campiUtente[5] .
            "   " .
            "FROM  " .
            $utenteTab . "  " .
            "WHERE  " .
            $campiUtente[1] . " = ? "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $username, $nome, $cognome, $bio);
            $utente = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiUtente[0]] = $email;
                $temp[$campiUtente[1]] = $username;
                $temp[$campiUtente[3]] = $nome;
                $temp[$campiUtente[4]] = $cognome;
                $temp[$campiUtente[5]] = $bio;
                array_push($utente, $temp);
            }
            return $utente;
        } else {
            return null;
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
            ////Controllo se esiste, non restituiva error nel caso in cui si passava un codice non esistente nel db.
            //Potrebbe anche essere eliminato il controllo perchè dovrebbe essere impossibibile passare un codice non esistente dall' app.
            return null;
        } else {
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $codice_sondaggio);
            $result = $stmt->execute();
            $stmt->store_result();

            return $result;
        }
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

    public function visualizzaRispostePerDomanda($cod_domanda)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];

        /* "risposta" => [
            "codice_risposta",
            "descrizione",
            "num_like",
            "num_dislike",
            "cod_utente",
            "cod_domanda"
        ],*/


        //QUERY: "SELECT * FROM `risposta` WHERE ID = 'value'"
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[5] . " = ?" .
             "ORDER BY " . $campi[0] . " DESC "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_domanda);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_risposta, $descrizione, $num_like, $num_dislike, $cod_utente, $cod_domanda);
            $risposte = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_risposta;
                $temp[$campi[1]] = $descrizione;
                $temp[$campi[2]] = $num_like;
                $temp[$campi[3]] = $num_dislike;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_domanda;
                array_push($risposte, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $risposte
            }
            return $risposte; //ritorno array $risposte riempito con i risultati della query effettuata
        } else {
            return null;
        }
    }


    public function visualizzaStatisticheDomanda($cod_utente)
    {

        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];

        //Query = select *from 'domanda' where id_domanda = 'value'
        /* "domanda" => [
                    "codice_domanda",
                    "dataeora",
                    "timer",
                    "titolo",
                    "descrizione",
                    "cod_utente",
                    "cod_categoria",
                    "cod_preferita"
                ],*/
        $query = (
            "SELECT " .
            "  COUNT(*) AS num_domande, " .
            $campi[6] . " " .
            "FROM " .
            $domandaTab . " " .
            "WHERE " .
            $campi[5] . " = ? " .
            " GROUP BY " . $campi[6] . " " .
            " ORDER BY " . $campi[6] . " " .
            "LIMIT 3"

        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_domande, $cod_categoria);
            $domande = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp["num_domande"] = $num_domande;
                $temp[$campi[6]] = $cod_categoria;
                array_push($domande, $temp);

            }
            return $domande;
        } else {
            return null;
        }

    }

    public function visualizzaCategoria($codice_categoria)
    {
        $categoriaTab = $this->tabelleDB[2];
        $campi = $this->campiTabelleDB[$categoriaTab];
        //QUERY: SELECT email, username, nome, cognome, bio FROM `utente` WHERE Email = 'value'
        /*   "categoria" => [
            "codice_categoria",
            "titolo"
        ],*/
        $query = (
            "SELECT *" .
            "FROM " .
            $categoriaTab . " " .
            "WHERE " .
            $campi[0] . "= ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_categoria);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_categoria, $titolo);
            $categorie = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $codice_categoria;
                $temp[$campi[1]] = $titolo;
                array_push($categorie, $temp);
            }
            return $categorie;
        } else {
            return null;
        }
    }


    public function visualizzaTOTStatisticheDomanda($cod_utente)
    {

        $DomandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$DomandaTab];

        /*SELECT COUNT(*) AS num_domande, cod_categoria FROM domanda WHERE cod_utente ="gmailverificata"
        GROUP BY cod_categoria ORDER BY cod_categoria
         "codice_domanda",
                "dataeora",
                "timer",
                "titolo",
                "descrizione",
                "cod_utente",
                "cod_categoria",
                "cod_preferita"
        */
        $query = (
            "SELECT " .
            "  COUNT(*) AS num_risposte, " .
            $campi[6] . " " .
            "FROM " .
            $DomandaTab . " " .
            "WHERE " .
            $campi[5] . " = ? " .
            " GROUP BY " . $campi[6] . " " .
            " ORDER BY " . $campi[6] . " "


        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_domande, $cod_categoria);
            $domande = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp["num_domande"] = $num_domande;
                $temp[$campi[6]] = $cod_categoria;
                array_push($domande, $temp);

            }
            return $domande;
        } else {
            return null;
        }

    }




    public function visualizzaStatisticherisposta($cod_utente)
    {

        $RispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$RispostaTab];
        $DomandaTab = $this->tabelleDB[4];
        $campiD = $this->campiTabelleDB[$DomandaTab];

        /*SELECT COUNT(*) AS num_risposte, cod_categoria FROM risposta ris JOIN domanda dom
        WHERE ris.cod_domanda = dom.codice_domanda AND ris.cod_utente = "gmailverificata"
        GROUP BY dom.cod_categoria ORDER BY dom.cod_categoria LIMIT 3

         "codice_domanda",
                "dataeora",
                "timer",
                "titolo",
                "descrizione",
                "cod_utente",
                "cod_categoria",
                "cod_preferita"


        "risposta" => [
            "codice_risposta",
            "descrizione",
            "num_like",
            "num_dislike",
            "cod_utente",
            "cod_domanda"
        ],
        */
        $query = (
            "SELECT " .
            "  COUNT(*) AS num_domande, " .
            $DomandaTab . "." .  $campiD[6] . " " .
            "FROM " .
            $RispostaTab . " " .
            "JOIN " .
            $DomandaTab . " " .
            "WHERE " .
            $RispostaTab . "." .  $campi[5].  " = " . $DomandaTab . "." . $campiD[0] . " AND  " .
            $RispostaTab . "." . $campi[4] . " = ? " . " " .
            " GROUP BY " . $DomandaTab . "." . $campiD[6] . " " .
            " ORDER BY " . $DomandaTab . "." . $campiD[6] . " " .
            "LIMIT 3"
        );

      /*  $sondaggioTab . "." . $campiSondaggio[0] . ", " .
        $sondaggioTab . "." . $campiSondaggio[1] . ", " .
        $sondaggioTab . "." . $campiSondaggio[2] . ", " .
        $sondaggioTab . "." . $campiSondaggio[3] . " " .

        */
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_risposte, $cod_categoria);
            $risposte = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp["num_risposte"] = $num_risposte;
                $temp[$campiD[6]] = $cod_categoria;
                array_push($risposte, $temp);

            }
            return $risposte;
        } else {
            return null;
        }

    }



    public function visualizzaStatisticheTOTrisposta($cod_utente)
    {

        $RispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$RispostaTab];
        $DomandaTab = $this->tabelleDB[4];
        $campiD = $this->campiTabelleDB[$DomandaTab];

        /*SELECT COUNT(*) AS num_risposte, cod_categoria FROM risposta ris JOIN domanda dom
        WHERE ris.cod_domanda = dom.codice_domanda AND ris.cod_utente = "gmailverificata"
        GROUP BY dom.cod_categoria ORDER BY dom.cod_categoria

         "codice_domanda",
                "dataeora",
                "timer",
                "titolo",
                "descrizione",
                "cod_utente",
                "cod_categoria",
                "cod_preferita"


        "risposta" => [
            "codice_risposta",
            "descrizione",
            "num_like",
            "num_dislike",
            "cod_utente",
            "cod_domanda"
        ],
        */
        $query = (
            "SELECT " .
            "  COUNT(*) AS num_domande, " .
            $campiD[6] . " " .
            "FROM " .
            $RispostaTab . " " .
            "JOIN " .
            $DomandaTab . " " .
            "WHERE " .
            $campi[5]=$campiD[0] . " " .
                $campi[5] . " = ? " .
                " GROUP BY " . $campiD[6] . " " .
                " ORDER BY " . $campiD[6] . " "


        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($num_risposte, $cod_categoria);
            $risposte = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp["num_risposte"] = $num_risposte;
                $temp[$campiD[6]] = $cod_categoria;
                array_push($risposte, $temp);

            }
            return $risposte;
        } else {
            return null;
        }

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
            $campiScelta[0] . "= ? " . " ".
              "AND " .
              $campiScelta[3] . "= ?  "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii",   $codice_scelta, $cod_sondaggio);

        $result = $stmt->execute();
        return $result;
    }

    public function scegliRispostaPreferita($codice_domanda, $cod_preferita)
    {

        $tabella = $this->tabelleDB[4];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  UPDATE Domanda
        //                        SET titolo=$titolo_inserito,dataeora=$valore,timer=$valore,  descrizione = $descrizione_inserita,     cod_categoria=$valore.
        //
        //                        WHERE = $Id_domanda_selezionata
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
        $stmt->bind_param("ii", $cod_preferita, $codice_domanda  );
        return $stmt->execute();
    }

    //Visto che non è ancora presente in DB, la si crea
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


    public function controlloGiaVotato($cod_risposta, $cod_utente)
    {
        $valutazioneTab = $this->tabelleDB[11]; //Tabella per la query
        $campi = $this->campiTabelleDB[$valutazioneTab]; //Campi per la query
        //QUERY: "SELECT email FROM utente WHERE email = ?
        $query = (
            "SELECT *" .
              " " .
            "FROM " .
            $valutazioneTab . " " .
            "WHERE " .
            $campi[1] . " = ? " .
            "AND " .  " " .
            $campi[2] . " = ? "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $cod_utente, $cod_sondaggio);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        //Se ha trovato un match tra la mail inserita e la tab utente, restituisce una bool TRUE
        return $stmt->num_rows > 0;
    }



    public function controlloGiaValutatoRisposta($cod_utente, $cod_risposta)
    {
        $valutazioneTab = $this->tabelleDB[10]; //Tabella per la query
        $campi = $this->campiTabelleDB[$valutazioneTab]; //Campi per la query
        //QUERY: "SELECT email FROM utente WHERE email = ?

        /*"valutazione" => [
            "cod_risposta",
            "cod_utente",
            "tipo_like"
        ],*/
        $query = (
            "SELECT " .
            " " .
            " $campi[2] " .
            "FROM " .
            $valutazioneTab . " " .
            "WHERE " .
            $campi[1] . " = ? " .
            "AND " .  " " .
            $campi[0] . " = ? "
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $cod_utente, $cod_risposta);
        $stmt->execute();

        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($tipo_like);
            $valutazione = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[2]] = $tipo_like;
                array_push($valutazione, $temp);

            }
            return $valutazione;
        } else {
            return null;
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

        echo "QUERY: " . $query;

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

        echo "QUERY: " . $query;

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i",  $codice_risposta);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }



}
?>