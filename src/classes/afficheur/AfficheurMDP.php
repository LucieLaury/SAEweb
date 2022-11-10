<?php

namespace iutnc\netVOD\afficheur;
use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\BadPasswordException;
use iutnc\netVOD\exception\InvalidTokenException;

class AfficheurMDP extends Afficheur
{
    /**
     * @throws BadPasswordException
     * @throws InvalidTokenException
     */
    public function execute(): string
    {
        $html = "";
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $html = <<<end
                <script src="javascript/register.js"></script>
                <form method="post">
                  <div class="flex flex-col w-80 mx-auto mt-32">
                    <label class="ml-1 font-bold text-center">Nouveau mot de passe</label>
                    <input class="shadow rounded my-8" placeholder="Nouveau mot de passe" type="password" name="firstPWD" id="firstPWD" required>
                    <input class="shadow rounded" placeholder="Confirmer"type="password" name="secondPWD" id="secondPWD" onchange="verifPWD()" required>
                    <button class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400" type="submit" id="button" disabled>Valider</button>
                  </div> 
                </form>
            end;
        } else {
            $token = $_GET['token'];
            $pwd = filter_var($_POST['firstPWD']);
            $mail = $_GET['mail'];
            Authentification::changeForgotPWD($token, $mail, $pwd);
        }
        return $html;
    }
}