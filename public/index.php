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
    $codice_scadenza = $requestData['codice_risposta'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaRisposta($codice_scadenza);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Risposte" => $responseData)));
        //metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});



/**** ENDPOINT ****/




















// Run app = ho riempito $app e avvio il servizio REST
$app->run();

?>
