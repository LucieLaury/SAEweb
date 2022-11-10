<?php

namespace iutnc\netVOD\afficheur;

use Exception;
use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\InvalidUserException;

class AfficheurOubli extends Afficheur
{
    public function execute(): string
    {
        $html = "";
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $html = <<<end
                <form method="post">
                <div class="flex flex-col w-80 mx-auto mt-32">
                    <label class="ml-1 font-bold text-center">Entrez votre email</label>
                    <input class="shadow rounded" type="email" name="email" required>
                    <button class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400" type="submit">Valider</button> 
                    </div>
                </form>
            end;
        } else {
            $mail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
            try {
                Authentification::generateToken($mail, 1);
            } catch (InvalidUserException|Exception $e) {
                print "<script>console.log('$e->getMessage()');</script>";
            }
        }
        return $html;
    }
}