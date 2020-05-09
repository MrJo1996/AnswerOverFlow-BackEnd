<?php
class EmailHelperAltervista
{
    public function __construct()
    {
    }


    //Funzione per inviare un'email con la nuova password
    function sendResetPasswordEmail($email, $password){

        $messaggio = "Usa questa password temporanea";

        $linkLogin = 'https://www.unimolshare.it/login.php';
        $emailTo = "chrispeluso98@gmail.com"; //$email
        $subject = "AnswerOverFLow - Recupero Password";
        $message   = '<html><body><h1>AnswerOverFLow</h1><div>';
        $message   .= $messaggio.':<br/><br/><b>'.$password.'</div><br/><div>Avvia la tua app /*'.$linkLogin.'*/ ed accedi con la nuova password.</div></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e){
            return false;
        }

    }

    function sendPropostaCategoriaEmail($selezione, $proposta){
        $messaggio = "Abbiamo ricevuto una nuova proposta per una categoria o sottocategoria!";

        $emailTo = "francesco.iafi@gmail.com";
        $subject = "AnswerOverFlow - Nuova proposta per categoria o sottocategoria";
        $message = '<html><body><h1>AnswerOverFLow</h1><div>';
        $message .= $messaggio . '<br><br>Categoria selezionata: ' . $selezione . '<br>Nuova proposta: ' . $proposta . '</div></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try{
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch(Exception $e){
            return false;
        }
    }

    //Funzione per inviare un'email di conferma dell'account
    function sendConfermaAccount($email, $link){

        $messaggio = 'Hai appena richiesto di iscriverti ad UnimolShare!<br>Conferma la tua iscrizione col seguente link:';
        $linkLogin = 'https://www.unimolshare.it/login.php';
        $emailTo = "andreacb94@gmail.com";
        $subject = "UnimolShare - Conferma registrazione";
        $message   = '<html><body><h1>UnimolShare</h1><div>';
        $message   .= $messaggio.'<br/><br/>'.$link.'</div><br/><div>Vai su '.$linkLogin.' per entrare.</div></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e){
            return false;
        }

    }

}