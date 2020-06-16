<?php

class EmailHelperAltervista
{
    public function __construct()
    {
    }

    //Funzione per inviare un'email con la nuova password
    function sendResetPasswordEmail($email, $password)
    {
        $emailTo = $email;
        $subject = "AnswerOverflow - Recupero Password";
        $message = '<html lang=""><body><h1>AnswerOverflow</h1><div>Usa questa password temporanea: </div><b>' . $password . '</b><br><br></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function sendPropostaCategoriaEmail($proposta)
    {
        $messaggio = "Abbiamo ricevuto una nuova proposta per una categoria!";

        $emailTo = "answeroverflow@gmail.com";
        $subject = "AnswerOverflow - Nuova proposta per categoria";
        $message = '<html lang=""><body><h1>AnswerOverflow</h1><div>';
        $message .= $messaggio . '<br><br>Categoria proposta: ' . $proposta . '<br><br></div></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    function inviaSegnalazione($segnalazione, $utente_segnalato, $email_utente_segnalato)
    {
        $messaggio = "Un utente ha segnalato questo profilo";

        $emailTo = "answeroverflow@gmail.com";
        $subject = "AnswerOverFLow - Segnalazione Utente";
        $message = '<html lang=""><body><h1>AnswerOverFLow</h1><div>';
        $message .= $messaggio . ':<br><br>Username: ' . $utente_segnalato . '<br>' . 'E-mail:' . $email_utente_segnalato . '<br><br>Segnalazione inviata per: ' . $segnalazione . '<br><br></div></body></html>';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($emailTo, $subject, $message, $headers);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}