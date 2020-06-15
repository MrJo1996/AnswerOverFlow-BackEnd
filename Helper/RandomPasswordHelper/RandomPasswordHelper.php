<?php

class RandomPasswordHelper
{

    public function __construct()
    {
    }

    //Generatore automatico di password random
    function generatePassword($length)
    {
        return str_shuffle(bin2hex(openssl_random_pseudo_bytes($length)));
    }

}