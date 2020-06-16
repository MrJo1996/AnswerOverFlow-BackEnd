<?php

class DBSearchServices
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

    //Seleziono tutte le categorie
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

    public function ricercaDomanda($stringa)
    {

        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];

        //Query = select *from 'domanda' where id_domanda = 'value'
        $myString = $stringa;
        $myArray = str_split($myString);
        $wherePart = "where ";
        foreach ($myArray as $key => $character) {
            if ($key == 0) {
                $wherePart .= " titolo REGEXP '[" . $character . "]'";
            } else {
                $wherePart .= "and titolo REGEXP '[" . $character . "]'";
            }
        }

        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $domandaTab . " " .
            $wherePart
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);

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
            foreach ($domande as $key => $value) {
                similar_text(strtolower($value['titolo']), strtolower($stringa), $percent);
                $domande[$key]['similarita'] = $percent;
            }

            usort($domande, function ($a, $b) {
                if ($a["similarita"] == $b["similarita"])
                    return (0);
                return (($a["similarita"] > $b["similarita"]) ? -1 : 1);
            });
            return $domande;
        } else {
            return null;
        }

    }

    public function ricercaSondaggioKeyword($stringa)
    {

        $sondaggioTab = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$sondaggioTab];

        //Query = select *from 'domanda' where id_domanda = 'value'
        $myString = $stringa;
        $myArray = str_split($myString);
        $wherePart = "where ";
        foreach ($myArray as $key => $character) {
            if ($key == 0) {
                $wherePart .= " titolo REGEXP '[" . $character . "]'";
            } else {
                $wherePart .= "and titolo REGEXP '[" . $character . "]'";
            }
        }
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $sondaggioTab . " " .
            $wherePart
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
            $domande = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[0]] = $codice_sondaggio;
                $temp[$campi[1]] = $dataeora;
                $temp[$campi[2]] = $titolo;
                $temp[$campi[3]] = $timer;


                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_categoria;

                array_push($domande, $temp);

            }
            foreach ($domande as $key => $value) {
                similar_text(strtolower($value['titolo']), strtolower($stringa), $percent);
                $domande[$key]['similarita'] = $percent;
            }

            usort($domande, function ($a, $b) {
                if ($a["similarita"] == $b["similarita"])
                    return (0);
                return (($a["similarita"] > $b["similarita"]) ? -1 : 1);
            });
            return $domande;
        } else {
            return null;
        }

    }

    public function ricercaUtenteKeyword($stringa)
    {
        $userTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$userTab];

        //Query = select *from 'domanda' where id_domanda = 'value'
        $myString = $stringa;
        $myArray = str_split($myString);
        $wherePart = "where ";
        foreach ($myArray as $key => $character) {
            if ($key == 0) {
                $wherePart .= " username REGEXP '[" . $character . "]'";
            } else {
                $wherePart .= "and username REGEXP '[" . $character . "]'";
            }
        }
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $userTab . " " .
            $wherePart
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $username, $password, $nome, $cognome, $bio, $attivo, $avatar);
            $users = array(); //controlla
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[0]] = $email;
                $temp[$campi[1]] = $username;
                //  $temp[$campi[2]] = $password;
                $temp[$campi[3]] = $nome;
                $temp[$campi[4]] = $cognome;
                $temp[$campi[5]] = $bio;
                $temp[$campi[6]] = $attivo;
                $temp[$campi[7]] = $avatar;

                array_push($users, $temp);

            }
            foreach ($users as $key => $value) {
                similar_text(strtolower($value['username']), strtolower($stringa), $percent);
                $users[$key]['similarita'] = $percent;
            }

            usort($users, function ($a, $b) {
                if ($a["similarita"] == $b["similarita"])
                    return (0);
                return (($a["similarita"] > $b["similarita"]) ? -1 : 1);
            });
            return $users;
        } else {
            return null;
        }

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


    public function trovaUltimoMessaggioInviato($cod_chat, $msg_utente_id)
    {
        $messaggiTab = $this->tabelleDB[9];
        $campiMessaggio = $this->campiTabelleDB[$messaggiTab];

        $query = (
            "SELECT " .
            " * " .
            "FROM " .
            $messaggiTab . "  " .
            "WHERE " .
            " " .
            $campiMessaggio[4] . " = ? " . "  " . " AND " . "  " . $campiMessaggio[5] . " = ? " .
            "ORDER BY " . $campiMessaggio[1] . " DESC LIMIT 1");

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $cod_chat, $msg_utente_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_messaggio, $dataeora, $testo, $visualizzato, $cod_chat, $msg_utente_id);

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campiMessaggio[0]] = $codice_messaggio;
                $temp[$campiMessaggio[1]] = $dataeora;
                $temp[$campiMessaggio[2]] = $testo;
                $temp[$campiMessaggio[3]] = $visualizzato;
                $temp[$campiMessaggio[4]] = $cod_chat;
                $temp[$campiMessaggio[5]] = $msg_utente_id;


                // array_push($msg_array, $temp);
            }
        }
        return $temp;
    }

    public function trovaChatContrario($cod_utente0, $cod_utente1)
    {
        $chatTab = $this->tabelleDB[8];
        $campiChat = $this->campiTabelleDB[$chatTab];

        $query = (
            "SELECT  " .
            $campiChat[0] .
            " FROM  " .
            $chatTab . "  " .
            "WHERE " .
            "  " .
            $campiChat[2] . " = ? " . "  " . " AND " . "  " . $campiChat[1] . " = ? ");

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $cod_utente0, $cod_utente1);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_chat);
            if ($stmt->fetch()) {
                return $cod_chat;

            }
        } else  return null;
    }

    public function trovaChat($cod_utente0, $cod_utente1)
    {
        $chatTab = $this->tabelleDB[8];
        $campiChat = $this->campiTabelleDB[$chatTab];

        //QUERY:  SELECT id FROM chat WHERE FK_Utente0 = $cod_utente0 AND FK_Utente1=        $cod_utente1
        //               					OR           	        FK_Utente1 = $cod_utente0
        //                                                      AND FK_Utente0 = $cod_utente1
        $query = (
            "SELECT  " .
            $campiChat[0] .
            " FROM " .
            $chatTab . "  " .
            "WHERE " .
            "  " .
            $campiChat[1] . " = ? " . "  " . " AND " . "  " . $campiChat[2] . " = ? ");

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $cod_utente0, $cod_utente1);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($cod_chat);
            if ($stmt->fetch()) {
                return $cod_chat;

            }
        } else return null;

    }

}