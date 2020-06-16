<?php

class DBViewServices
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

    //Seleziono tutte le chats di un utente secondo un determinato ID
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

    //Seleziono tutte le risposte valutate secondo una categoria e una mail
    public function selezionaRisposteValutate($email, $cod_categoria)
    {
        $domandaTab = $this->tabelleDB[4];
        $rispostaTab = $this->tabelleDB[5];
        $campiDomanda = $this->campiTabelleDB[$domandaTab];
        $campiRisposta = $this->campiTabelleDB[$rispostaTab];

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

    //Conto tutte le valutazioni di una risposta
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
            $risposta = array();
            while ($stmt->fetch()) {
                $temp = array();
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
            $campiMessaggio[4] . " = ? " .
            " ORDER BY " . $campiMessaggio[1] . "  ASC "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_chat);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_messaggio, $dataeora, $testo, $visualizzato, $cod_chat, $msg_utente_id);
            $messaggio = array();

            while ($stmt->fetch()) {
                $temp = array();

                $temp[$campiMessaggio[0]] = $codice_messaggio;
                $temp[$campiMessaggio[1]] = $dataeora;
                $temp[$campiMessaggio[2]] = $testo;
                $temp[$campiMessaggio[3]] = $visualizzato;
                $temp[$campiMessaggio[4]] = $cod_chat;
                $temp[$campiMessaggio[5]] = $msg_utente_id;

                array_push($messaggio, $temp);
            }
            return $messaggio;
        } else {
            return null;
        }
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

    public function visualizzarisposteperdomanda($cod_domanda)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campiRisposta = $this->campiTabelleDB[$rispostaTab];
        //query: SELECT sondaggio.cod_sondaggio, sondaggio.dataeora, sondaggio.titolo, sondaggio.timer,
        // WHERE sondaggio.cod_categoria = " ? "
        $query = (
            "SELECT " . "  " .
            $rispostaTab . "." . $campiRisposta[0] . ",  " .
            $rispostaTab . "." . $campiRisposta[1] . ",  " .
            $rispostaTab . "." . $campiRisposta[2] . ",  " .
            $rispostaTab . "." . $campiRisposta[3] . ",  " .
            $rispostaTab . "." . $campiRisposta[4] . ",  " .
            $rispostaTab . "." . $campiRisposta[5] . "  " .
            "FROM " . "  " . $rispostaTab . " " .
            "WHERE " . "  " . $rispostaTab . "." . $campiRisposta[5] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_domanda);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_risposta, $descrizione, $num_like, $num_dislike, $cod_utente, $cod_domanda);
            $risposta = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiRisposta[0]] = $codice_risposta;
                $temp[$campiRisposta[1]] = $descrizione;
                $temp[$campiRisposta[2]] = $num_like;
                $temp[$campiRisposta[3]] = $num_dislike;
                $temp[$campiRisposta[4]] = $cod_utente;
                $temp[$campiRisposta[5]] = $cod_domanda;
                array_push($risposta, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $risposta;//ritorno array $sondaggio riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }


    public function visualizzaDomandeHome()
    {


        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];

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

    public function visualizzaSondaggiHome()
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$sondaggioTab];

        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $sondaggioTab . " " .
            "WHERE " .
            $campi[3] . " > '00:00:00' " .
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
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
            $sondaggi = array();
            while ($stmt->fetch()) {
                $temp = array(); //
                //indicizzo key con i dati nell'array
                $temp[$campi[0]] = $codice_sondaggio;
                $temp[$campi[1]] = $dataeora;
                $temp[$campi[2]] = $titolo;
                $temp[$campi[3]] = $timer;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_categoria;
                array_push($sondaggi, $temp);
            }
            return $sondaggi;
        } else {
            return null;
        }

    }

    public function risposte($codice_domanda)
    {
        $rispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$rispostaTab];

        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $rispostaTab . " " .
            "WHERE " .
            $campi[5] . " = ? " .
            "ORDER BY  " . $campi[0] . "  DESC "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_domanda);
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

    public function visualizzaStatisticherisposta($cod_utente)
    {

        $RispostaTab = $this->tabelleDB[5];
        $campi = $this->campiTabelleDB[$RispostaTab];
        $DomandaTab = $this->tabelleDB[4];
        $campiD = $this->campiTabelleDB[$DomandaTab];

        $query = (
            "SELECT " .
            "  COUNT(*) AS num_domande, " .
            $DomandaTab . "." . $campiD[6] . " " .
            "FROM " .
            $RispostaTab . " " .
            "JOIN " .
            $DomandaTab . " " .
            "WHERE " .
            $RispostaTab . "." . $campi[5] . " = " . $DomandaTab . "." . $campiD[0] . " AND  " .
            $RispostaTab . "." . $campi[4] . " = ? " . " " .
            " GROUP BY " . $DomandaTab . "." . $campiD[6] . " " .
            " ORDER BY " . $DomandaTab . "." . $campiD[6] . " " .
            "LIMIT 3"
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

    public function visualizzaMieDomande($cod_utente)
    {
        $domandaTab = $this->tabelleDB[4];
        $campi = $this->campiTabelleDB[$domandaTab];
        $query = (
            "SELECT " .
            $domandaTab . "." . $campi[0] . ", " .
            $domandaTab . "." . $campi[1] . ", " .
            $domandaTab . "." . $campi[2] . ", " .
            $domandaTab . "." . $campi[3] . ", " .
            $domandaTab . "." . $campi[4] . ", " .
            $domandaTab . "." . $campi[5] . ", " .
            $domandaTab . "." . $campi[6] . ", " .
            $domandaTab . "." . $campi[7] . " " .
            "FROM " . $domandaTab . " " .
            "WHERE " . $domandaTab . "." . $campi[5] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria, $cod_preferita);
            $domanda = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_domanda;
                $temp[$campi[1]] = $dataeora;
                $temp[$campi[2]] = $timer;
                $temp[$campi[3]] = $titolo;
                $temp[$campi[4]] = $descrizione;
                $temp[$campi[5]] = $cod_utente;
                $temp[$campi[6]] = $cod_categoria;
                $temp[$campi[7]] = $cod_preferita;
                array_push($domanda, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $domanda;//ritorno array $sondaggio riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }

    public function visualizzaMieiSondaggi($cod_utente)
    {
        $sondaggioTab = $this->tabelleDB[6];
        $campi = $this->campiTabelleDB[$sondaggioTab];
        $query = (
            "SELECT " .
            $sondaggioTab . "." . $campi[0] . ", " .
            $sondaggioTab . "." . $campi[1] . ", " .
            $sondaggioTab . "." . $campi[2] . ", " .
            $sondaggioTab . "." . $campi[3] . ", " .
            $sondaggioTab . "." . $campi[4] . ", " .
            $sondaggioTab . "." . $campi[5] . " " .
            "FROM " . $sondaggioTab . " " .
            "WHERE " . $sondaggioTab . "." . $campi[4] . " = ?"
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_sondaggio, $dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
            $sondaggio = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_sondaggio;
                $temp[$campi[1]] = $dataeora;
                $temp[$campi[2]] = $titolo;
                $temp[$campi[3]] = $timer;
                $temp[$campi[4]] = $cod_utente;
                $temp[$campi[5]] = $cod_categoria;
                array_push($sondaggio, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $sondaggio
            }
            return $sondaggio;//ritorno array $sondaggio riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }

    public function controlloGiaVotato($cod_utente, $cod_sondaggio)
    {
        $votanteTab = $this->tabelleDB[11]; //Tabella per la query
        $campi = $this->campiTabelleDB[$votanteTab]; //Campi per la query
        //QUERY: "SELECT email FROM utente WHERE email = ?
        $query = (
            "SELECT *" .
            " " .
            "FROM " .
            $votanteTab . " " .
            "WHERE " .
            $campi[1] . " = ? " .
            "AND " . " " .
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

        $query = (
            "SELECT " .
            " * " .
            "FROM " .
            $valutazioneTab . " " .
            "WHERE " .
            $campi[2] . " = ? " .
            "AND " . " " .
            $campi[1] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $cod_utente, $cod_risposta);
        $stmt->execute();

        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_valutazione, $cod_risposta, $cod_utente, $tipo_like);
            $valutazione = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $codice_valutazione;
                $temp[$campi[1]] = $cod_risposta;
                $temp[$campi[2]] = $cod_utente;
                $temp[$campi[3]] = $tipo_like;
                array_push($valutazione, $temp);
            }
            return $valutazione;
        } else {
            return null;
        }
    }


}