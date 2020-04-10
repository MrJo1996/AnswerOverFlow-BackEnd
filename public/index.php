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
        //Metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

// endpoint: /controlloEmail OK
$app->post('/controlloEmail', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $responseData['data'] = $db->controlloEmail($email);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "L'email è presente nel DB";
        $response->getBody()->write(json_encode(array("Esito esistenza email" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La mail non è presente nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /recuperaPassword funziona ma
// è da creare il set di funzioni che gestiscono la situazione del recupero password
$app->post('/recuperaPassword', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $password = $requestData['password'];
    $responseData['data'] = $db->recuperaPassword($email, $password);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Esito cambio Password" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

// endpoint: /aChiAppartieniRisposta OK
$app->post('/aChiAppartieniRisposta', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_risposta = $requestData['codice_risposta'];
    $responseData['data'] = $db->aChiAppartieniRisposta($codice_risposta);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Domanda della risposta" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La risposta inserita non esiste nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /aChiAppartieniDomanda OK
$app->post('/aChiAppartieniDomanda', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_domanda = $requestData['codice_domanda'];
    $responseData['data'] = $db->aChiAppartieniDomanda($codice_domanda);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Categoria della domanda" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La domanda inserita non esiste nel DB';
        return $response->withJson($responseData);
    }
});

// endpoint: /controlloStats OK
$app->post('/controlloStats', function (Request $request, Response $response)
{
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    $codice_categoria = $requestData['codice_categoria'];
    $responseData['data'] = $db->controlloStats($email, $codice_categoria);

    if($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Statistiche" => $responseData)));
        $newResponse = $response-> withHeader('Content-type', 'application/json');
        return $newResponse;
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Le statistiche ricercate non sono state trovate';
        return $response->withJson($responseData);
    }
});

// endpoint: /aggiornaStats funziona ma
// è da creare il set di funzioni che gestisce la nuova statistica
$app->post('/aggiornaStats', function (Request $request, Response $response)
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
});

// endpoint: /insertStats OK
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
});

/**** ENDPOINT ****/




















// Run app = ho riempito $app e avvio il servizio REST
$app->run();

?>
