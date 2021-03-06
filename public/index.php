<?php
/* In questo file php vengono elencati tutti gli endpoint disponibili al servizio REST */

//Import Slim e le sue librerie
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../vendor/autoload.php';
require '../DB/DBConnectionManager.php';
require '../DB/DBAuthServices.php';
require '../DB/DBInsertServices.php';
require '../DB/DBDeleteServices.php';
require '../DB/DBSearchServices.php';
require '../DB/DBViewServices.php';
require '../DB/DBUpdateServices.php';

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
    }

    Per il testing in Postman metodi Delete:
       -selezionare come method DELETE
       -comporre l'url come sempre ma aggiungendo "/x" dove "x" è il paramentro da passare alla funzione, per intenderci il paramentro che veniva specificato nel body dei metodi post.
       -send
    */


/*************** LISTA ENDPOINT **************/

// endpoint: /visualizzaRisposta
$app->post('/visualizzarisposta', function (Request $request, Response $response) {
    $db = new DBViewServices();
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
$app->post('/Visualizzarisposteperdomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_domanda = $requestData['cod_domanda'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzarisposteperdomanda($cod_domanda);

    if ($responseData['data'] == null) {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);

    } else {

        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Risposte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    }
});


// endpoint: /visualizzaRisposta
$app->post('/visualizzaRisposteUtente', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $email = $requestData['email'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaRisposteUtente($email);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Risposte" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/visualizzaChats', function (Request $request, Response $response) {
    $db = new DBViewServices();

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

// endpoint: /controlloEmail
$app->post('/controlloEmail', function (Request $request, Response $response) {
    $db = new DBAuthServices();
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

$app->post('/proponi_categoria', function (Request $request, Response $response) {
    $requestData = $request->getParsedBody();
    $proposta = $requestData['proposta'];
    $emailSender = new EmailHelperAltervista();
    $responseData = array();

    if ($emailSender->sendPropostaCategoriaEmail($proposta)) {
        $responseData['error'] = false;
        $responseData['message'] = "Proposta inviata con successo";
    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Impossibile inviare la proposta";
    }
    return $response->withJson($responseData);
});

//endpoint /recupero password/modifica password
$app->post('/recupero', function (Request $request, Response $response) {

    $db = new DBAuthServices();

    $requestData = $request->getParsedBody();
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

// endpoint: /aChiAppartieniRisposta
$app->post('/aChiAppartieniRisposta', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

// endpoint: /aChiAppartieniDomanda
$app->post('/aChiAppartieniDomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

// endpoint: /ricercaScelteSondaggio
$app->post('/ricercaScelteSondaggio', function (Request $request, Response $response) {
    $db = new DBSearchServices();
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
        $responseData['message'] = 'Le scelte del sondaggio non sono state inserite';
        return $response->withJson($responseData);
    }
});

// endpoint: /inserisciScelteDelSondaggio
$app->post('/inserisciScelteSondaggio', function (Request $request, Response $response) {
    $db = new DBInsertServices();
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
    $db = new DBSearchServices();
    $request->getParsedBody();
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

// endpoint: /controlloStats
$app->post('/controlloStats', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

// endpoint: /selezionaRisposteValutate
// Seleziona i codici delle risposte che hanno ricevuto almeno una valutazione
$app->post('/selezionaRisposteValutate', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

// endpoint: /contaRisposteValutate
// Conta il numero di risposte che hanno ricevuto una valutazione
$app->post('/contaRisposteValutate', function (Request $request, Response $response) {
    $db = new DBViewServices();
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
$app->post('/contaValutazioni', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

// endpoint: /aggiornaStats
$app->post('/aggiornaStats', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $dbIns = new DBInsertServices();
    $dbUp = new DBUpdateServices();

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

        if ($statistiche != null) {
            if ($dbUp->aggiornaStats($email, $codice_categoria, $nLike, $nDislike, $nValutazioni)) {
                $responseData['error'] = false;
                $responseData['message'] = "Stats aggiornate correttamente";
            } else {
                $responseData['error'] = true;
                $responseData['message'] = "Il DB non risponde correttamente all' aggiornamento delle statistiche";
            }
        } elseif ($dbIns->insertStats($email, $codice_categoria, $nLike, $nDislike, $nValutazioni)) {
            $responseData['error'] = false;
            $responseData['message'] = "Statistiche create e like inserito";
        } else {
            $responseData['error'] = true;
            $responseData['message'] = "Probabilmente l'utente o la categoria inserita non esiste";
        }
    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Non ha senso aggiornare le statistiche perchè l'utente non
        esiste o non ha risposto a domande nelle categoria immessa";
    }
    return $response->withJson($responseData);
});

// endpoint: /visualizzaNumLikeRisposta
$app->post('/visualizza_num_like', function (Request $request, Response $response) {
    $db = new DBViewServices();
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
    $db = new DBViewServices();
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

// endpoint: /modifica_num_like
$app->post('/modifica_num_like', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    //Risposta del servizio REST
    $responseData = array();
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

// endpoint: /modificaNumDisLike
$app->post('/modifica_num_dislike', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    $dislike = $requestData['num_dislike'];

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

// endpoint: /modificaTipo_like
$app->post('/modifica_tipo_like', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $cod_risposta = $requestData['cod_risposta'];
    $cod_utente = $requestData['cod_utente'];
    $tipo_like = $requestData['tipo_like'];

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
$app->delete('/cancellaVal/{cod_risposta},{cod_utente}', function (Request $request, Response $response) {

    $db = new DBDeleteServices();

    $cod_risposta = $request->getAttribute("cod_risposta");
    $cod_utente = $request->getAttribute("cod_utente");

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

});


// endpoint: /modificaRisposta
$app->post('/modificaRisposta', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

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

//endpoint: / visualizza sondaggio per categoria

$app->post('/visualizzaSondaggioPerCategoria', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

//endpoint: /visualizzaProfilo
$app->post('/visualizzaProfilo', function (Request $request, Response $response) {
    $db = new DBViewServices();

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

//endpoint: /modificaProfilo
$app->post('/modificaProfilo', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

    $requestData = $request->getParsedBody();

    $username = $requestData['username'];
    $password = $requestData['password'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $bio = $requestData['bio'];
    $email = $requestData['email'];
    $avatar = $requestData['avatar'];

    $responseData = array();
    if (isset($requestData['password']))
        $responseDB = $db->modificaProfilo($username, $password, $nome, $cognome, $bio, $email, $avatar);
    else
        $responseDB = $db->modificaParteProfilo($username, $nome, $cognome, $bio, $email, $avatar);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile effettuare la modifica';
    }
    return $response->withJson($responseData);
});

// endpoint: /visualizzaDomanda
$app->post('/visualizzadomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
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

/// endpoint: /registrazione
$app->post('/registrazione', function (Request $request, Response $response) {

    $db = new DBAuthServices();
    $requestData = $request->getParsedBody();

    $email = $requestData['email'];
    $username = $requestData['username'];
    $password = $requestData['password'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $bio = $requestData['bio'];
    $avatar = $requestData['avatar'];

    $responseData = array();
    $responseDB = $db->registrazione($email, $username, $password, $nome, $cognome, $bio, $avatar);

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

    $db = new DBViewServices();
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

$app->post('/ricercaDomandaKeyword', function (Request $request, Response $response) {

    $db = new DBSearchServices();
    $requestData = $request->getParsedBody();

    $keyword = $requestData['keyword'];

    $responseData['data'] = $db->ricercaDomanda($keyword);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Domanda" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/ricercaUserKeyword', function (Request $request, Response $response) {

    $db = new DBSearchServices();
    $requestData = $request->getParsedBody();

    $keyword = $requestData['keyword'];

    $responseData['data'] = $db->ricercaUtenteKeyword($keyword);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("utente" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/ricercaSondaggioKeyword', function (Request $request, Response $response) {

    $db = new DBSearchServices();
    $requestData = $request->getParsedBody();

    $keyword = $requestData['keyword'];

    $responseData['data'] = $db->ricercaSondaggioKeyword($keyword);

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
    $db = new DBAuthServices();

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
    $db = new DBViewServices();

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
    $db = new DBInsertServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST


    $titolo = $requestData['titolo'];
    $timer = $requestData['timer'];
    $descrizione = $requestData['descrizione'];
    $cod_utente = $requestData['cod_utente'];
    $cod_categoria = $requestData['cod_categoria'];

    //Risposta del servizio REST
    $responseData = array();

    $result = $db->inserisciDomanda($timer, $titolo, $descrizione, $cod_utente, $cod_categoria);
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


//endpoint: /inserisciSondaggio
$app->post('/inseriscisondaggio', function (Request $request, Response $response) {

    $db = new DBInsertServices();
    $dbSearch = new DBSearchServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio

    $timer = $requestData['timer'];
    $dataeora = $requestData['dataeora'];
    $cod_utente = $requestData['cod_utente'];
    $cod_categoria = $requestData['cod_categoria'];
    $titolo = $requestData['titolo'];

    $responseData = array();


    $responseDB = $db->inserisciSondaggio($dataeora, $titolo, $timer, $cod_utente, $cod_categoria);
    if ($responseDB) {
        $responseData['data'] = $dbSearch->prendiCodiceSondaggio($cod_utente);
        $responseData['error'] = false;
        $responseData['message'] = 'Sondaggio inserito con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Sondaggio non inserito'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);
});


//ENDPOINT invia messaggio
$app->post('/inviamessaggio', function (Request $request, Response $response) {

    $db = new DBInsertServices();

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
    $db = new DBUpdateServices();

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

    $db = new DBInsertServices();

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

    $db = new DBInsertServices();

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

$app->delete('/timerScadutoSondaggi', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $dbDel = new DBDeleteServices();

    $responseData = array();

    $responseData['data'] = $db->visualizzaSondaggi();
    $cancellazioni = true;
    $responseData['eliminati'] = array();

    if ($responseData['data']) {
        $now = strtotime("now");
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $timerSondaggio = strtotime($responseData['data'][$i]['timer']) - strtotime("TODAY");
            $fineSondaggio = strtotime($responseData['data'][$i]['dataeora']) + $timerSondaggio;
            if ($fineSondaggio < $now) {
                $format = 'd/m/Y H:i:s';
                $cod_oldSondaggio = $responseData['data'][$i]['codice_sondaggio'];
                if (!$responseDB = $dbDel->cancellaSondaggio($cod_oldSondaggio))
                    $cancellazioni = false;
                array_push($responseData['eliminati'], $cod_oldSondaggio);
            }
        }
        if ($cancellazioni) {
            $responseData['error'] = false;
            $responseData['message'] = "Le eliminazioni necessarie sono state effettuate";
        } else {
            $responseData['error'] = true;
            $responseData['message'] = "La cancellazione non è andata a buon fine";
        }
    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Non è possibile comunicare con il server";
    }
    return $response->withJson($responseData);
});

//endpoint: /rimuoviRisposta
$app->delete('/rimuoviRisposta/{codice_risposta}', function (Request $request, Response $response) {
    $db = new DBDeleteServices();
    $codice = $request->getAttribute("codice_risposta");
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
});


//endpoint: /rimuoviProfilo
$app->delete('/eliminaProfilo/{email}', function (Request $request, Response $response) {

    $db = new DBDeleteServices();

    $email = $request->getAttribute("email");

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


//endpoint: /cancellaSondaggio
$app->delete('/cancellaSondaggio/{codice_sondaggio}', function (Request $request, Response $response) {

    $db = new DBDeleteServices();

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

    $db = new DBInsertServices();

    $requestData = $request->getParsedBody();

    $testo = $requestData['testo'];
    $visualizzato = $requestData['visualizzato'];
    $cod_chat = $requestData['cod_chat'];
    $msg_utente_id = $requestData['msg_utente_id'];


    $responseData = array();


    $responseDB = $db->inserisciMessaggio($testo, $visualizzato, $cod_chat, $msg_utente_id);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Messaggio inserito con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Messaggio non inserito'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData);

});


$app->post('/trovachat', function (Request $request, Response $response) {
    $db = new DBSearchServices();

    $requestData = $request->getParsedBody();

    $cod_utente0 = $requestData['cod_utente0'];
    $cod_utente1 = $requestData['cod_utente1'];

    $responseData['data'] = $db->trovaChat($cod_utente0, $cod_utente1);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Chat" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['data'] = $db->trovaChatContrario($cod_utente0, $cod_utente1);
        if ($responseData['data'] != null) {
            $responseData['error'] = false;
            $responseData['message'] = 'Elemento visualizzato con successo';
            $response->getBody()->write(json_encode(array("Chat" => $responseData)));
            return $response->withHeader('Content-type', 'application/json');
        } else {

            $responseData['data'] = null;
            $responseData['error'] = true;
            $responseData['message'] = 'Errore imprevisto';
            $response->getBody()->write(json_encode(array("Chat" => $responseData)));
            return $response->withHeader('Content-type', 'application/json');
        }
    }
});


$app->post('/trovaUltimoMessaggioInviato', function (Request $request, Response $response) {
    $db = new DBSearchServices();

    $requestData = $request->getParsedBody();

    $cod_chat = $requestData['cod_chat'];
    $msg_utente_id = $requestData['msg_utente_id'];

    $responseData['data'] = $db->trovaUltimoMessaggioInviato($cod_chat, $msg_utente_id);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
        $response->getBody()->write(json_encode(array("Messaggio" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/modificaDomanda', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

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
    $db = new DBSearchServices();

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

//ricercaprofiloperusername
$app->post('/ricercaprofiloperusername', function (Request $request, Response $response) {

    $db = new DBSearchServices();
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

    $db = new DBDeleteServices();

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

    $db = new DBDeleteServices();

    $codice = $request->getAttribute("codice_sondaggio");

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
});

//endpoint: /modificaPassword
$app->post('/modificaPassword', function (Request $request, Response $response) {
    $db = new DBUpdateServices();

    $requestData = $request->getParsedBody();


    $password = $requestData['password'];
    $email = $requestData['email'];

    $responseData = array();
    $responseDB = $db->modificaPasssword($password, $email);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Modifica effettuata con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile effettuare la modifica';
    }
    return $response->withJson($responseData);
});

$app->post('/creachat', function (Request $request, Response $response) {

    $db = new DBSearchServices();
    $dbIns = new DBInsertServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $cod_utente0 = $requestData['cod_utente0'];
    $cod_utente1 = $requestData['cod_utente1'];

    $responseData = array();

    $responseDB = $dbIns->creaChat($cod_utente0, $cod_utente1);
    if ($responseDB) {

        $responseData['cod_chat'] = $db->trovaChat($cod_utente0, $cod_utente1);
        if ($responseData['cod_chat'] == null) {
            $responseData['cod_chat'] = $db->trovaChatContrario($cod_utente0, $cod_utente1);
        }
        $responseData['error'] = false;
        $responseData['message'] = 'Chat creata con successo'; //Messaggio di esito positivo
        $response->getBody()->write(json_encode(array("Chat" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Chat non creata'; //Messaggio di esito negativo
        return $response->withJson($responseData);
    }
});


$app->post('/segnala_utente', function (Request $request, Response $response) {
    $requestData = $request->getParsedBody();
    $email_utente_segnalato = $requestData['email_utente_segnalato'];
    $utente_segnalato = $requestData['utente_segnalato'];
    $segnalazione = $requestData['segnalazione'];

    $emailSender = new EmailHelperAltervista();
    $responseData = array();

    if ($emailSender->inviaSegnalazione($segnalazione, $utente_segnalato, $email_utente_segnalato)) {
        $responseData['error'] = false;
        $responseData['message'] = "Segnalazione inviata con successo";
    } else {
        $responseData['error'] = true;
        $responseData['message'] = "Impossibile inviare la segnalazione";
    }
    return $response->withJson($responseData);
});


$app->post('/visualizzadomandehome', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $responseData['data'] = $db->visualizzaDomandeHome();

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Domande visualizzate con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Domande" => $responseData)));

        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/visualizzasondaggihome', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $responseData['data'] = $db->visualizzaSondaggiHome();

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Sondaggi visualizzate con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Sondaggi" => $responseData)));
        //Metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/risposteperdomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $codice_domanda = $requestData['codice_domanda'];
    $responseData['data'] = $db->risposte($codice_domanda);

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

$app->post('/visualizzastatistichedomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    //Controllo la domanda dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaStatisticheDomanda($cod_utente);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Domande" => $responseData)));

        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/visualizzacategoria', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $codice_categoria = $requestData['codice_categoria'];
    //Controllo la domanda dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaCategoria($codice_categoria);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Categoria visualizzata con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Categoria" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});


$app->post('/visualizzaTOTStatisticheDomanda', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    //Controllo la domanda dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaStatisticheDomanda($cod_utente);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Domande" => $responseData)));

        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }


});

$app->post('/visualizzaStatisticherisposta', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaStatisticherisposta($cod_utente);

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


$app->post('/visualizzaStatisticheTOTrisposta', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaStatisticherisposta($cod_utente);

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

$app->post('/votasondaggio', function (Request $request, Response $response) {
    $db = new DBInsertServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_scelta = $requestData['codice_scelta'];
    $cod_sondaggio = $requestData['cod_sondaggio'];

    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->votaSondaggio($codice_scelta, $cod_sondaggio);
    if ($responseDB) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Sondaggio votato con successo'; //Messaggio di esiso positivo

    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile votare"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

$app->post('/visualizzaUltimoMessaggio', function (Request $request, Response $response) {
    $db = new DBViewServices();

    $requestData = $request->getParsedBody();
    $cod_chat = $requestData['cod_chat'];

    $responseData['data'] = $db->visualizzaLastMessaggio($cod_chat);
    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = 'Elemento visualizzato con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore imprevisto';
    }
    return $response->withJson($responseData);
});

$app->post('/sceglirispostapreferita', function (Request $request, Response $response) {
    $db = new DBInsertServices();

    $requestData = $request->getParsedBody();

    $codice_domanda = $requestData['codice_domanda'];
    $cod_preferita = $requestData['cod_preferita'];

    $responseData = array();
    $responseDB = $db->scegliRispostaPreferita($codice_domanda, $cod_preferita);

    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Risposta preferita scelta con successo';
    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Impossibile scegliere la risposta';
    }
    return $response->withJson($responseData);
});

$app->post('/visualizzaDomandeMie', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaMieDomande($cod_utente);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Domande" => $responseData)));

        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

$app->post('/visualizzaSondaggiMiei', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaMieiSondaggi($cod_utente);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Sondaggi" => $responseData)));

        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});


$app->post('/inserisciVotante', function (Request $request, Response $response) {
    $db = new DBInsertServices();
    $requestData = $request->getParsedBody();
    $cod_scelta = $requestData['cod_scelta'];
    $cod_utente = $requestData['cod_utente'];
    $cod_sondaggio = $requestData['cod_sondaggio'];
    $responseData['data'] = $db->inserisciNuovoVotante($cod_scelta, $cod_utente, $cod_sondaggio);

    if ($responseData['data']) {
        $responseData['error'] = false;
        $responseData['message'] = "Operazione andata a buon fine";
        $response->getBody()->write(json_encode(array("Inserimento votante" => $responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Il votante non è stato inserito';
        return $response->withJson($responseData);
    }
});


$app->post('/controllogiavotato', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    $cod_sondaggio = $requestData['cod_sondaggio'];
    $responseData['data'] = $db->controlloGiaVotato($cod_utente, $cod_sondaggio);

    if ($responseData['data'] != null) {
        $responseData['error'] = false;
        $responseData['message'] = "L'cod_utente è presente nel DB";
        $response->getBody()->write(json_encode(array($responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'La mail non è presente nel DB';
        $response->getBody()->write(json_encode(array($responseData)));
        return $response->withHeader('Content-type', 'application/json');
    }
});


$app->post('/controllogiavalutatorisposta', function (Request $request, Response $response) {
    $db = new DBViewServices();
    $requestData = $request->getParsedBody();
    $cod_utente = $requestData['cod_utente'];
    $cod_risposta = $requestData['cod_risposta'];
    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->controlloGiaValutatoRisposta($cod_utente, $cod_risposta);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Gia hai votato '; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array($responseData)));
        return $response->withHeader('Content-type', 'application/json');
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non hai votato';
        $response->getBody()->write(json_encode(array($responseData)));
        return $response->withHeader('Content-type', 'application/json');
    }
});

$app->delete('/eliminaVal/{codice_valutazione}', function (Request $request, Response $response) {

    $db = new DBDeleteServices();

    $codice_valutazione = $request->getAttribute("codice_valutazione");

    $responseData = array();

    $responseDB = $db->eliminaValutazione($codice_valutazione);
    if ($responseDB) {
        $responseData['error'] = false;
        $responseData['message'] = 'Valutazione rimossa con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true;
        $responseData['message'] = 'Errore, valutazione non rimossa'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData);
});

$app->post('/togli_like', function (Request $request, Response $response) {
    $db = new DBDeleteServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->togliLike($codice_risposta);
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

$app->post('/togli_dislike', function (Request $request, Response $response) {
    $db = new DBDeleteServices();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_risposta = $requestData['codice_risposta'];
    $dislike = $requestData['num_dislike'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB = $db->togliDislike($codice_risposta);
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

/**** ENDPOINT ****/

// Run app
$app->run();
