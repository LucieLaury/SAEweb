<?php

namespace iutnc\netVOD\afficheur;

use Exception;
use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\AlreadyRegisteredEmailException;
use iutnc\netVOD\exception\BadPasswordException;
use iutnc\netVOD\exception\CardNotExistingException;
use iutnc\netVOD\exception\InvalidUserException;
use iutnc\netVOD\exception\NotAnEmailException;

class AfficheurRegistrer extends Afficheur
{

    public function execute(): string
    {
        $html = "";
        if($_SERVER['REQUEST_METHOD'] === "GET") {
            $html = <<<END
                <div class='max-w-2xl' style='text-align: left; margin-left: 3%; padding: 1%'>
                <script src="javascript/register.js"></script>
                <form action="" method="post">
                    <label>Identifiant</label>
                    <input type="email" name="email" style='margin-bottom: 1%' class="shadow rounded" placeholder="bernard@mail.com" required>
                    <br>
                    <label>Nom</label>
                    <input type="text" name="nom" style='margin-bottom: 1%' class="shadow rounded" placeholder="LERMITE" required><br>
                    <lab>Prénom</lab>
                    <input type="text" name="prenom" style='margin-bottom: 1%'class="shadow rounded" placeholder="Bernard" required><br>
                    <label>Numéro de carte bancaire</label>
                    <input type="text" name="noCarte" style='margin-bottom: 1%' class="shadow rounded" placeholder="0000 0000 0000 0000"><br>
                    <label>Mot de passe</label>
                    <input id="firstPWD" type="password" style='margin-bottom: 1%' class="shadow rounded" name="password" required><br>
                   
                    <label>Confirmer le mot de passe</label>
                    <input id="secondPWD" type="password" style='padding: 1%' class="shadow rounded" name="confPwd" onchange="verifPWD()">
                    <button id="button" type="submit" class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400" disabled>S'enregistrer</button>
                </form>
                <a href="?action=default"><button class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400">Vous avez déjà un compte?</button></a>
                </div>
            END;
        } else {
            $mail = $_POST['email'];
            $mdp = filter_var($_POST['password']);
            $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
            $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
            $noCarte = filter_var($_POST['noCarte'], FILTER_SANITIZE_NUMBER_INT);

            try {
                Authentification::register($mail, $mdp, $nom, $prenom, $noCarte);
                Authentification::loadProfile($mail);
                try {
                    Authentification::generateToken($mail);
                } catch (Exception|InvalidUserException $e) {
                    print "<script>console.log('$e')</script>";
                }
            } catch (AlreadyRegisteredEmailException|BadPasswordException|NotAnEmailException|CardNotExistingException $e) {
                $html = $e->getMessage();
            }

        }
        return $html;
    }
}