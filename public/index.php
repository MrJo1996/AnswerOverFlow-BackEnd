<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 11/05/18
 * Time: 21:11
 */

/* In questo file php vengono elencati tutti gli endpoint disponibili al servizio REST */

//Importiamo Slim e le sue librerie
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../vendor/autoload.php';
require '../DB/DBConnectionManager.php';
require '../DB/DBUtenti.php';

/*require '../Helper/EmailHelper/EmailHelper.php';*/
require '../Helper/EmailHelper/EmailHelperAltervista.php';
require '../Helper/RandomPasswordHelper/RandomPasswordHelper.php';

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

// Instantiate the app -
$settings = require __DIR__ . '/../src/settings.php';
$app = new App($settings); //"Contenitore" per gli endpoint da riempire


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

/*  Gli endpoint sono delle richieste http accessibili al Client gestite poi dal nostro Server REST.
    Tra i tipi di richieste http, le più usate sono:
    - get (richiesta dati -> elaborazione server -> risposta server)
    - post (invio dati criptati e richiesta dati -> elaborazione server -> risposta server)
    - delete (invio dato (id di solito) e richiesta eliminazione -> elaborazione server -> risposta server)

    Slim facilita per noi la gestione della richiesta http mettendo a disposizione funzioni facili da implementare
    hanno la forma:

    app->"richiesta http"('/nome endpoint', function (Request "dati inviati dal client", Response "dati risposti dal server") {

        //logica del servizio  ---- (COME SI FA IL JS)

        return "risposta";

    } */

/*************** LISTA DI ENDPOINT **************/

// endpoint: /visualizzaRisposta OK
$app->post('/visualizzarisposta', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_risposta = $requestData['codice_risposta'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaRisposta($codice_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Risposte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

// endpoint: /controlloEmail OK
$app->post('/controlloEmail', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $responseData['data'] = $db->controlloEmail($email);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "L'email è presente nel DB";
        $response->getBody()->write(json_encode(array("Esito esistenza email" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La mail non è presente nel DB';
        return $response->withJson($responseData);
    }
});

//endpoint /recupero password/modifica password
//ci sono da apportare modifiche alla funzione EmailSender
$app->post('/recupero', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];

    //Risposta del servizio REST
    $responseData = array();
    $emailSender = new EmailHelperAltervista(); //È da modificare il link con il quale viene inviata la mail di recupero
    $randomizerPassword = new RandomPasswordHelper();

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->controlloEmail($email)) {

        $nuovaPassword = $randomizerPassword->generatePassword(6);

        if ($db->recuperaPassword($email, $nuovaPassword)) {
            if ($emailSender->sendResetPasswordEmail($email, $nuovaPassword)) {
                $responseData['error'] = false;
                $responseData['message'] = "Email di recupero password inviata";
            } else {
                $responseData['error'] = true; //Campo errore = true
                $responseData['message'] = "Impossibile inviare l'email di recupero";
            }
        } else { //Se la connessione al DB fallisce
            $responseData['error'] = true;
            $responseData['message'] = 'Impossibile comunicare col Database';
        }

    } else { //Se le credenziali non sono corrette
        $responseData['error'] = true;
        $responseData['message'] = 'Email non presente nel DB';
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /aChiAppartieniRisposta OK
$app->post('/aChiAppartieniRisposta', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_risposta = $requestData['codice_risposta'];
    $responseData['data'] = $db->aChiAppartieniRisposta($codice_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Domanda della risposta" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La risposta inserita non esiste nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /aChiAppartieniDomanda OK
$app->post('/aChiAppartieniDomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_domanda = $requestData['codice_domanda'];
    $responseData['data'] = $db->aChiAppartieniDomanda($codice_domanda);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Categoria della domanda" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La domanda inserita non esiste nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /ricercaScelteDelSondaggio OK
$app->post('/ricercaScelteDelSondaggio', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_sondaggio = $requestData['codice_sondaggio'];
    $responseData['data'] = $db->ricercaScelteDelSondaggio($codice_sondaggio);

    if ($responseData['data']) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Statistiche" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le scelte del sondaggio non sono presenti nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /controlloStats OK
$app->post('/controlloStats', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $responseData['data'] = $db->controlloStats($email, $codice_categoria);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Statistiche" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le statistiche ricercate non sono state trovate';
        return $response->withJson($responseData);
    }
});

// endpoint: /aggiornaStats   OK
$app->post('/aggiornaStats', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $ultima_valutazione = $requestData['valutazione'];
    $statistiche = $db->controlloStats($email, $codice_categoria);
    if ($statistiche != null) { //Se la mail e la categoria hanno già una stats
        $nuova_valutazione = $statistiche[0]['sommatoria_valutazioni'] + $ultima_valutazione;
        $nuovo_n_valutazioni = $statistiche[0]['numero_valutazioni'] + 1;
        if ($db->aggiornaStats($email, $codice_categoria, $nuova_valutazione, $nuovo_n_valutazioni)) { //vengono aggiornate
            $responseData['error'] = false;
            $responseData['message'] = "Statistiche aggiornate";
        } else { //non è stato possibile aggiornarle
            $responseData['error'] = true;
            $responseData['message'] = "Il DB non risponde correttamente all' aggiornamento delle statistiche";
        }
    } elseif ($db->insertStats($email, $codice_categoria, $ultima_valutazione)) { //Altrimenti la si crea
        $responseData['error'] = false;
        $responseData['message'] = "Statistiche create";
    } else { //non è stato possibile crearle
        $responseData['error'] = true;
        $responseData['message'] = "Probabilmente l'utente o la categoria inserita non esiste";
    }
    return $response->withJson($responseData);
});

/* endpoint: per testare aggiornaStats
$app->post('/aggiornaStats1', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $valutazione = $requestData['sommatoria_valutazioni'];
    $n_valutazioni = $requestData['numero_valutazioni'];
    $responseData['data'] = $db->aggiornaStats($email, $codice_categoria, $valutazione, $n_valutazioni);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Statistiche aggiornate";
        $response->getBody()->write(json_encode(array("Statistiche aggiornate" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le statistiche ricercate non sono state trovate';
        return $response->withJson($responseData);
    }
}); */

/* endpoint per testare insertStats
$app->post('/insertStats', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $valutazione = $requestData['sommatoria_valutazioni'];
    $responseData['data'] = $db->insertStats($email, $codice_categoria, $valutazione);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Statistiche inserite";
        $response->getBody()->write(json_encode(array("Statistiche inserite" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le statistiche ricercate non sono state trovate';
        return $response->withJson($responseData);
    }
});*/

// endpoint: /modificaVotazione : numero 5
$app->post('/modificavalutazione', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    $valutazione = $requestData['valutazione'];
    echo "PARAMS:";
    echo $codice_risposta . "     ";
    echo $valutazione . "     ";
    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->modificaValutazione($codice_risposta, $valutazione);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($responseDB) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Modifica effettuata'; //Messaggio di esiso positivo

    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile effettuare la modifica"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


// endpoint: /modificaRisposta :numero 8
$app->post('/modificaRisposta', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    $descrizione = $requestData['descrizione'];

//Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->modificaRisposta($codice_risposta, $descrizione);
//Controllo la risposta dal DB e compilo i campi della risposta
    if ($responseDB) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Modifica effettuata'; //Messaggio di esiso positivo

    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile effettuare la modifica"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

//endpoint: / visualizza sondaggio per categoria :non in elenco

$app->post('/visualizzaSondaggioPerCategoria', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $cod_categoria = $requestData['cod_categoria'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaSondaggioPerCategoria($cod_categoria);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Sondaggio" => $responseData)));
        //metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /visualizzaProfilo...OK
$app->post('/visualizzaProfilo', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
    $email = $requestData['email'];

    $responseData['data'] = $db->visualizzaProfilo($email);
    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Profilo" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /modificaProfilo...OK
$app->post('/modificaProfilo', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $username = $requestData['username'];
    $password = $requestData['password'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $bio = $requestData['bio'];
    $email = $requestData['email'];

    $responseData = array();
    $responseDB = $db->modificaProfilo($username, $password, $nome, $cognome, $bio, $email);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile effettuare la modifica';
    }
    return $response->withJson($responseData);
});

// endpoint: /visualizzaDomanda OK
$app->post('/visualizzadomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_domanda = $requestData['codice_domanda'];
    //Controllo la domanda dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaDomanda($codice_domanda);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Domande" => $responseData)));
        //Metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }


});

// endpoint: /registrazione
$app->post('/registrazione', function (Request $request, Response $response) {

    $db = new DBUtenti();
    $requestData = $request->getParsedBody();

    $email = $requestData['email'];
    $username = $requestData['username'];
    $password = $requestData['password'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $bio = $requestData['bio'];

    $responseData = array();
    $responseDB = $db->registrazione($email, $username, $password, $nome, $cognome, $bio);

    if ($responseDB == 1) {
        $responseData['error'] = false;
        $responseData['message'] = 'Registrazione avvenuta con successo';
    } else if ($responseDB == 2) {
        $responseData['error'] = true;
        $responseData['message'] = 'Account già  esistente!';
    }

    return $response->withJson($responseData);
});

//endpoint: /visualizzaSondaggio
$app->post('/visualizzaSondaggio', function (Request $request, Response $response) {

    $db = new DBUtenti();
    $requestData = $request->getParsedBody();

    $codice_sondaggio = $requestData['codice_sondaggio'];

    $responseData['data'] = $db->visualizzaSondaggio($codice_sondaggio);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Sondaggio" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});


$app->post('/login', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $username = $requestData['username'];
    $password = $requestData['password'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->login($username, $password);
    if ($responseData['data']) { //Se l'utente esiste ed e' corretta la password
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Accesso effettuato'; //Messaggio di esiso positivo

    } else { //Se le credenziali non sono corrette
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Credenziali errate'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


$app->post('/visualizzaMessaggi', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
    $cod_chat = $requestData['cod_chat'];

    $responseData['data'] = $db->visualizzaMessaggi($cod_chat);
    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Messaggi" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});


//endpoint inserisci domanda: OK
$app->post('/inserisciDomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST


    $titolo = $requestData['titolo'];
    $dataeora = $requestData['dataeora'];
    $timer = $requestData['timer'];
    $descrizione = $requestData['descrizione'];
    $cod_utente = $requestData['cod_utente'];
    $cod_categoria = $requestData['cod_categoria'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare


    $result = $db->inserisciDomanda($dataeora, $timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($result) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Inserimento avvenuto con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Inserimento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


//n11(TEAM cassetta) endpoint: /inserisciSondaggio  CASSETTA  OK
$app->post('/inseriscisondaggio', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $timer = $requestData['timer'];
    $dataeora = $requestData['dataeora'];
    $cod_utente = $requestData['cod_utente'];
    $cod_categoria = $requestData['cod_categoria'];
    $titolo = $requestData['titolo'];

    $responseData = array();


    $responseDB = $db->inserisciSondaggio($dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Sondaggio inserito con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Sondaggio non inserito'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});


//ENDPOINT invia messaggio(NON FUNZIONA)

$app->post('/inviamessaggio', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $testo = $requestData['testo'];
    $cod_utente0 = $requestData['cod_utente0'];
    $cod_utente1 = $requestData['cod_utente1'];
    $dataeora = $requestData['dataeora'];
    $visualizzato = $requestData['visualizzato'];

    $responseData = array();

    $responseDB = $db->inviaMessaggio($testo, $cod_utente0, $cod_utente1, $dataeora, $visualizzato);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Messaggio inviato con successo';


    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Messaggio non inviato'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});


// endpoint: /modificaSondaggio

$app->post('/modificaSondaggio', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_sondaggio = $requestData['codice_sondaggio'];
    $dataeora = $requestData['dataeota'];
    $titolo = $requestData['titolo'];
    $cod_categoria = $requestData['cod_categoria'];

//Risposta del servizio REST
    $responseData = array();
    $responseDB = $db->modificaSondaggio($codice_sondaggio, $dataeora, $titolo, $cod_categoria);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata';

    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Impossibile effettuare la modifica";
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /inserisicVotazione
$app->post('/inserisciVotazione', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $valutazione = $requestData['valutazione'];

    $responseData = array();


    $responseDB = $db->inserisciVotazione($valutazione);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Votazione inserita con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Votazione non inserita'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});


//endpoint: /ricercaSondaggio non aperto
$app->post('/ricercaSondaggio', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $categoria = $requestData['categoria'];
    $titoloSondaggio = $requestData['titolo'];

    $responseData['data'] = $db->ricercaSondaggio($categoria, $titoloSondaggio);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Sondaggi trovati" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /ricercaDomanda non aperta
$app->post('/ricercaDomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $categoria = $requestData['categoria'];
    // $titoloDomanda = $requestData['titolo'];

    $responseData['data'] = $db->ricercaDomanda($categoria/*,$titoloDomanda*/);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Domande trovate" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /rimuoviRisposta
$app->delete('/rimuoviRisposta/{codice_risposta}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $codice = $request->getAttribute("codice_risposta");

    //Stampa codice passed
    echo "\n\n Codice passato: " . $codice;

    $responseData = array();

    $responseDB = $db->rimuoviRisposta($codice);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Risposta rimossa con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, risposta non rimossa'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

    /*Per il testing in Postman:
       -selezionare come method DELETE
       -comporre l'url come sempre ma aggiungendo "/x" dove "x" è il paramentro da passare alla funzione, per intenderci il paramentro che veniva specificato nel body dei metodi post.
       -send
    */
});


//endpoint: /rimuoviProfilo  OK
$app->delete('/eliminaProfilo/{email}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $email = $request->getAttribute("email");

//Stampa codice passed
    echo "\n\n email passata: " . $email;

    $responseData = array();

    $responseDB = $db->eliminaProfilo($email);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Profilo rimosso con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, utente non rimosso'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});


//endpoint: /cancellaSondaggio OK
$app->delete('/cancellaSondaggio/{codice_sondaggio}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $codice = $request->getAttribute("codice_sondaggio");


    $responseData = array();

    $responseDB = $db->cancellaSondaggio($codice);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Sondaggio rimosso con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, sondaggio non rimosso'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);
});

$app->post('/inseriscimessaggio', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST


    $dataeora = $requestData['dataeora'];
    $testo = $requestData['testo'];
    $visualizzato = $requestData['visualizzato'];
    $cod_chat = $requestData['cod_chat'];


    $responseData = array();


    $responseDB = $db->inserisciMessaggio($dataeora, $testo, $visualizzato, $cod_chat);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Messaggio inserito con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Messaggio non inserito'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});

$app->post('/modificaDomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $codice_domanda = $requestData['codice_domanda'];
    $dataeora = $requestData['dataeora'];
    $timer = $requestData['timer'];
    $titolo = $requestData['titolo'];
    $descrizione = $requestData['descrizione'];
    $cod_categoria = $requestData['cod_categoria'];

    $responseData = array();
    $responseDB = $db->modificaDomanda($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile effettuare la modifica';
    }
    return $response->withJson($responseData);

});

//endpoint: /ricercaDomandaAperta
$app->post('/ricercaDomandaAperta', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $categoria = $requestData['categoria'];
    $titoloDomanda = $requestData['titolo'];

    $responseData['data'] = $db->ricercaDomandaAperta($categoria, $titoloDomanda);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Domande trovate" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /ricercaSondaggioAperto
$app->post('/ricercaSondaggioAperto', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();

    $categoria = $requestData['categoria'];
    $titoloSondaggio = $requestData['titolo'];

    $responseData['data'] = $db->ricercaSondaggioAperto($categoria, $titoloSondaggio);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Sondaggi trovati" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//ricercaprofiloperusername
$app->post('/ricercaprofiloperusername', function (Request $request, Response $response) {

    $db = new DBUtenti();
    $requestData = $request->getParsedBody();

    $username = $requestData['username'];

    $responseData['data'] = $db->ricercaProfiloPerUsername($username);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Profilo visualizzato con successo';
        $response->getBody()->write(json_encode(array("Profilo" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//endpoint: /cancellaDomanda
$app->delete('/cancellaDomanda/{codice_domanda}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $codice = $request->getAttribute("codice_domanda");


    $responseData = array();

    $responseDB = $db->cancellaDomanda($codice);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Domanda rimossa con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, domanda non rimossa'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);
});


/**** ENDPOINT ****/


// Run app = ho riempito $app e avvio il servizio REST
$app->run();

?>