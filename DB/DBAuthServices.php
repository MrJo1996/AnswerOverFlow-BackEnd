<?php

class DBAuthServices
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


}