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

//endpoint: visualizzaRispostePerDomanda
$app->post('/visualizzarisposteperdomanda', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $cod_domanda = $requestData['cod_domanda'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaRispostePerDomanda($cod_domanda);

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


// endpoint: /visualizzaRisposta OK
$app->post('/visualizzaRisposteUtente', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaRisposteUtente($email);

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

$app->post('/visualizzaChats', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['codice_utente'];

    $responseData['data'] = $db->visualizzaChats($cod_utente);
    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Chats" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
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

$app->post('/proponi_cat_o_sottocat', function (Request $request, Response $response){
    $requestData = $request->getParsedBody();
    $selezione = $requestData['selezione'];
    $proposta = $requestData['proposta'];
    $emailSender = new EmailHelperAltervista();
    $responseData = array();

    if($emailSender->sendPropostaCategoriaEmail($selezione, $proposta)){
        $responseData['error'] = false;
        $responseData['message'] = "Proposta inviata con successo";
    }else{
        $responseData['error'] = true;
        $responseData['message'] = "Impossibile inviare la proposta";
    }
    return $response->withJson($responseData);
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

// endpoint: /ricercaScelteDelSondaggio


/*$app->post('/visualizzadomanda', function (Request $request, Response $response) {
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
*/
$app->post('/ricercaScelteSondaggio', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_sondaggio = $requestData['codice_sondaggio'];
    $responseData['data'] = $db->ricercaScelteDelSondaggio($codice_sondaggio);

    if ($responseData['data']) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Scelte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le scelte del sondaggio non sono presenti nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /inserisciScelteDelSondaggio
$app->post('/inserisciScelteSondaggio', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_sondaggio = $requestData['codice_sondaggio'];
    $descrizione = $requestData['descrizione'];
    $responseData['data'] = $db->insertScelte($descrizione, $codice_sondaggio);

    if ($responseData['data']) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Scelte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le scelte del sondaggio non sono state inserite';
        return $response->withJson($responseData);
    }
});

// endpoint: /ricercaCategorie
$app->post('/ricercaCategorie', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $responseData['data'] = $db->ricercaCategorie();

    if ($responseData['data']) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Categorie" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non è stato possibile comunicare col DB o non sono presenti Categorie';
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

// endpoint: /selezionaRisposteValutate OK
// Seleziona i codici delle risposte che hanno ricevuto almeno una valutazione
// Da utilizzare in AggiornaStats
$app->post('/selezionaRisposteValutate', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $responseData['data'] = $db->selezionaRisposteValutate($email, $codice_categoria);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Codici" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non è stato possibile trovare le risposte dell"utente o della categoria ricercata';
        return $response->withJson($responseData);
    }
});

// endpoint: /contaRisposteValutate OK
// Conta il numero di risposte che hanno ricevuto una valutazione
// Da utilizzare in AggiornaStats
$app->post('/contaRisposteValutate', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $responseData['data'] = count($db->selezionaRisposteValutate($email, $codice_categoria));

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Numero risposte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non è stato possibile trovare le risposte dell"utente o della categoria ricercata';
        return $response->withJson($responseData);
    }
});

// endpoint: /contaValutazioni OK
// Conta il numero di like e dislike ricevuti
// Da utilizzare in AggiornaStats
$app->post('/contaValutazioni', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $cods = $db->selezionaRisposteValutate($email, $codice_categoria);
    if ($cods != null) $responseData['data'] = $db->contaValutazioni($cods);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Numero risposte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non è stato possibile trovare le risposte dell"utente o della categoria ricercata';
        return $response->withJson($responseData);
    }
});

// endpoint: /aggiornaStats   OK
$app->post('/aggiornaStats', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $cods = $db->selezionaRisposteValutate($email, $codice_categoria);
    if ($cods != null) {
        $valutazioni = $db->contaValutazioni($cods);
        $nLike = $valutazioni ["num_like"];
        $nDislike = $valutazioni ["num_dislike"];
        $nValutazioni = count($cods);
        $statistiche = $db->controlloStats($email, $codice_categoria);

        if ($statistiche != null) { //Se la mail e la categoria hanno già una stats
            if ($db->aggiornaStats($email, $codice_categoria, $nLike, $nDislike, $nValutazioni)) { //vengono aggiornate
                $responseData['error'] = false;
                $responseData['message'] = "Stats aggiornate correttamente";
            } else { //non è stato possibile aggiornarle
                $responseData['error'] = true;
                $responseData['message'] = "Il DB non risponde correttamente all' aggiornamento delle statistiche";
            }
        } elseif ($db->insertStats($email, $codice_categoria, $nLike, $nDislike, $nValutazioni)) { //Altrimenti la si crea
            $responseData['error'] = false;
            $responseData['message'] = "Statistiche create e like inserito";
        } else { //non è stato possibile crearle
            $responseData['error'] = true;
            $responseData['message'] = "Probabilmente l'utente o la categoria inserita non esiste";
        }
    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Non ha senso aggiornare le statistiche perchè l'utente non
        esiste o non ha risposto a domande nelle categoria immessa";
    }
//    echo $valutazioni ["num_like"];

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

/* //endpoint per testare insertStats
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

// endpoint: /visualizzaNumLikeRisposta
$app->post('/visualizza_num_like', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_risposta = $requestData['codice_risposta'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaNumLikeRisposta($codice_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Numero like" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

// endpoint: /visualizzaNumDislikeRisposta
$app->post('/visualizza_num_dislike', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_risposta = $requestData['codice_risposta'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaNumDislikeRisposta($codice_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Numero dislike" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

// endpoint: /checkIfUserHasAlreadyEvaluatedResponse ( controlla se un utente ha gia valutato una risposta )
$app->post('/check_if_user_has_already_evaluated_response', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $cod_risposta = $requestData['cod_risposta'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->checkIfUserHasAlreadyEvaluatedResponse($cod_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Valutazioni" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});



// endpoint: /modificaNumLike : numero 5
$app->post('/modifica_num_like', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    echo "PARAMS:";
    echo $codice_risposta . "     ";
    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->modificaNumLike($codice_risposta);
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

// endpoint: /modificaNumDisLike : numero 5
$app->post('/modifica_num_dislike', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    $dislike = $requestData['num_dislike'];
    echo "PARAMS:";
    echo $codice_risposta . "     ";
    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->modificaNumDisLike($codice_risposta);
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

// endpoint: /modificaTipo_like : numero 5
$app->post('/modifica_tipo_like', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $cod_risposta = $requestData['cod_risposta'];
    $cod_utente = $requestData['cod_utente'];
    $tipo_like = $requestData['tipo_like'];
    echo "PARAMS:";
    echo $cod_risposta . "     ";
    echo $cod_utente . "     ";
    echo $tipo_like . "     ";
    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->modificaTipo_like($tipo_like, $cod_risposta, $cod_utente);
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

//endpoint: /cancella valutazione
$app->delete('/cancella_valutazione/{cod_risposta},{cod_utente}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $cod_risposta = $request->getAttribute("cod_risposta");
    $cod_utente = $request->getAttribute("cod_utente");

    //Stampa codice passed
    echo "\n\n Codice risposta passato: " . $cod_risposta;
    echo "\n\n Codice utente passato: " . $cod_utente;

    $responseData = array();

    $responseDB = $db->cancellaValutazione($cod_risposta, $cod_utente);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Valutazione rimossa con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, valutazione non rimossa'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

    /*Per il testing in Postman:
       -selezionare come method DELETE
       -comporre l'url come sempre ma aggiungendo "/x" dove "x" è il paramentro da passare alla funzione, per intenderci il paramentro che veniva specificato nel body dei metodi post.
       -send
    */
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
        $responseData['data'] = $db->prendiCodiceSondaggio($cod_utente);
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
    $titolo = $requestData['titolo'];
    $timer = $requestData['timer'];
    $codice_sondaggio = $requestData['codice_sondaggio'];

//Risposta del servizio REST
    $responseData = array();
    $responseDB = $db->modificaSondaggio($titolo, $timer, $codice_sondaggio);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata';

    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Impossibile effettuare la modifica";
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /inserisicValutazione
$app->post('/inserisci_valutazione', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $cod_risposta = $requestData['cod_risposta'];
    $cod_utente = $requestData['cod_utente'];
    $tipo_like = $requestData['tipo_like'];

    $responseData = array();


    $responseDB = $db->inserisciValutazione($cod_risposta, $cod_utente, $tipo_like);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Valutazione inserita con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Valutazione non inserita'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});

// endpoint: /inserisicRisposta
$app->post('/inserisci_risposta', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $descrizione = $requestData['descrizione'];
    $cod_utente = $requestData['cod_utente'];
    $cod_domanda = $requestData['cod_domanda'];

    $responseData = array();


    $responseDB = $db->inserisciRisposta($descrizione, $cod_utente, $cod_domanda);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Risposta inserita con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Risposta non inserita'; //Messaggio di esito negativo
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

//endpoint: /rimuove i sondaggi che hanno il timer scaduto
$app->delete('/timerScadutoSondaggi', function (Request $request, Response $response){
    $db = new DBUtenti();

    $responseData = array();

    $responseData['data'] = $db->visualizzaSondaggi();
    $cancellazioni = true;
    $responseData['eliminati'] = array();

    if ($responseData['data']){
        $now = strtotime("now");
        for ($i = 0; $i < count($responseData['data']); $i++){
            $timerSondaggio = strtotime($responseData['data'][$i]['timer']) - strtotime("TODAY");
            $fineSondaggio = strtotime($responseData['data'][$i]['dataeora']) + $timerSondaggio;
            if ($fineSondaggio < $now){
                $format = 'd/m/Y H:i:s';
                echo "\nVecchio sondaggio scaduto il " . date($format, $fineSondaggio);
                $cod_oldSondaggio = $responseData['data'][$i]['codice_sondaggio'];
                if (!$responseDB = $db->cancellaSondaggio($cod_oldSondaggio))
                    $cancellazioni = false;
                array_push($responseData['eliminati'], $cod_oldSondaggio);
            }
        }
        if ($cancellazioni){
            $responseData['error'] = false;
            $responseData['message'] = "Le eliminazioni necessarie sono state effettuate";
        }else{
            $responseData['error'] = true;
            $responseData['message'] = "La cancellazione non è andata a buon fine";
        }
    }else{
        $responseData['error'] = true;
        $responseData['message'] = "Non è possibile comunicare con il server";
    }
    return $response->withJson($responseData);
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
    $cod_preferita = $requestData['cod_preferita'];

    $responseData = array();
    $responseDB = $db->modificaDomanda($codice_domanda, $dataeora, $timer, $titolo, $descrizione, $cod_categoria, $cod_preferita);

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

//endpoint: /rimuoviSondaggio
$app->delete('/rimuoviSondaggio/{codice_sondaggio}', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $codice = $request->getAttribute("codice_sondaggio");

    //Stampa codice passed
    echo "\n\n Codice passato: " . $codice;

    $responseData = array();

    $responseDB = $db->rimuoviSondaggio($codice);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Sondaggio rimosso con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, sondaggio non rimosso'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

    /*Per il testing in Postman:
       -selezionare come method DELETE
       -comporre l'url come sempre ma aggiungendo "/x" dove "x" è il paramentro da passare alla funzione, per intenderci il paramentro che veniva specificato nel body dei metodi post.
       -send
    */
});

//endpoint: /modificaPassword...OK
$app->post('/modificaPassword', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();


    $password = $requestData['password'];
    $email = $requestData['email'];

    $responseData = array();
    $responseDB = $db->modificaPasssword($password,  $email);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile effettuare la modifica';
    }
    return $response->withJson($responseData);
});

// endpoint che serve per visualizzare le domande nella home
$app->post('/visualizzadomandehome', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    //Controllo la domanda dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaDomandeHome();

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Domande visualizzate con successo'; //Messaggio di esiso positivo
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

/**** ENDPOINT ****/


// Run app = ho riempito $app e avvio il servizio REST
$app->run();

?>