<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;

class AfficheurActivation extends Afficheur
{

    public function execute(): string
    {
        session_start();
        $token = $_GET['token'];
        $email = unserialize($_SESSION['user']);
        Authentification::activate($token, $email);
        return "";
    }
}