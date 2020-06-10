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
        $emailTo = $email;
        $subject = "AnswerOverflow - Recupero Password";
        $message   = '<html><body><h1>AnswerOverflow</h1><div>Usa questa password temporanea:</div><b>' . $password . '</b></b><br><br></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e){
            return false;
        }

    }

    function sendPropostaCategoriaEmail($proposta){
        $messaggio = "Abbiamo ricevuto una nuova proposta per una categoria!";

        $emailTo = "francesco.iafi@gmail.com";
        $subject = "AnswerOverFlow - Nuova proposta per categoria";
        $message = '<html><body><h1>AnswerOverflow</h1><div>';
        $message .= $messaggio . '<br><br>Categoria proposta: ' . $proposta . '<br><br></div></body></html>';
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