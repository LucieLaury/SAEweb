<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\InvalidTokenException;

class AfficheurActivation extends Afficheur
{

    /**
     * @throws InvalidTokenException
     */
    public function execute(): string
    {
        if($_SERVER['REQUEST_METHOD'] === "GET") {
            $html = <<<end
                <form method="post">
                    <div class="flex flex-col w-80 mx-auto mt-32">
                    <button class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400" type="submit">Activation du compte</button>
                    </div>
                </form>
            end;

        } else {
            session_start();
            $token = $_GET['token'];
            $user = unserialize($_SESSION['user']);
            $email = $user->email;
            Authentification::activate($token, $email);
            header("location:?action=accueil-utilisateur");
            $html = "";
        }

        return $html;
    }
}