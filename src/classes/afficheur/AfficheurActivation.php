<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;

class AfficheurActivation extends Afficheur
{

    public function execute(): string
    {
        if($_SERVER['REQUEST_METHOD'] === "GET") {
            $html = <<<end
                <form method="post">
                    <button type="submit">Activation du compte</button>
                </form>
            end;

        } else {
            session_start();
            $token = $_GET['token'];
            $user = unserialize($_SESSION['user']);
            $email = $user->email;
            Authentification::activate($token, $email);
            $html = "";
        }

        return $html;
    }
}