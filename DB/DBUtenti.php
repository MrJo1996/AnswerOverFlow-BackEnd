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
            "somma_valutazioni",
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
        //Se ha trovato un match tra la mail inserita e il DB restituisce una bool TRUE
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

    //Seleziono tutto il contenuto di risposta secondo un determinato ID
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
        //La funzione termina con l'esecuzione della query
        return $stmt->execute();
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
        //La funzione termina con l'esecuzione della query che mi restituisce il codice della domanda
        return $stmt;
    }

    //Prendo la categoria della domanda
    public function categoriaDomanda($id_domanda)
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
        //La funzione termina con l'esecuzione della query che mi restituisce il codice della categoria
        return $stmt;
    }

    //Se la tabella è gia presente aggiorno le stats, altrimenti ne crea una nuova
    public function aggiornaStats($id_utente, $id_categoria, $valutazione)
    {
        $statsTab = $this->tabelleDB[1];
        $campi = $this->campiTabelleDB[$statsTab];
        //QUERY: SELECT * FROM stats WHERE cod_utente = ? AND cod_categoria = ?
        $query = (
            "SELECT " .
            "* " .
            "FROM " .
            $statsTab . " " .
            "WHERE "  . " " .
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
        if($stmt->num_rows > 0){
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
        //L'utente non ha ancora una tabella relativa alla categoria di riferimento quindi la creo, la prossima volta verrà selezionata per poi
        //aggiornarla nel back end, se avrò indietro dei valori aggiorno
        else{
            //QUERY: INSERT INTO `stats` (`cod_utente`, `cod_categoria`, `media_voto`, `numero_risposte`) VALUES ('$id_utente', '$id_categoria', '$valutazione', '1');
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
            return $stmt->execute();
        }
    }

    //Visualizzo il profilo di un utente
    public function visualizzaProfilo($email){
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
    public function modificaProfilo($email, $username, $password, $nome, $cognome, $bio){
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];
        //QUERY: UPDATE `utente` SET `Nome`=[value-1], `Cognome`=[value-2],`Username`=[value-3],`Email`=[value-4],`Password`=[value-5],`Bio`=[value-6] WHERE Email = “email_utente_corrente”
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
}
?>