<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\AlreadyRegisteredEmailException;
use iutnc\netVOD\exception\BadPasswordException;
use iutnc\netVOD\exception\NotAnEmailException;

class AfficheurRegistrer extends Afficheur
{

    public function execute(): string
    {
        $html = "";
        if($_SERVER['REQUEST_METHOD'] === "GET") {
            $html = <<<END
                <script src="javascript/register.js"></script>
                <form action="" method="post">
                    <label>Identifiant</label>
                    <input type="email" name="email" placeholder="bernard@mail.com" required>
                    <br>
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="LERMITE" required><br>
                    <lab>Prenom</lab>
                    <input type="text" name="prenom" placeholder="Bernard" required><br>
                    <label>Numéro de carte bancaire</label>
                    <input type="text" name="noCarte" placeholder="0000 0000 0000 0000"><br>
                    <label>Password</label>
                    <input id="firstPWD" type="password" name="password" required><br>
                   
                    <label>Confirm Password</label>
                    <input id="secondPWD" type="password" name="confPwd" onchange="verifPWD()">
                    <button id="button" type="submit" disabled>S'enregistrer</button>
                </form>
                <a href="">Vous avez déjà un compte ?</a>
            END;
        } else {
            $mail = $_POST['email'];
            $mdp = filter_var($_POST['password']);
            $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
            $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
            $noCarte = filter_var($_POST['noCarte'], FILTER_SANITIZE_NUMBER_INT);

            try {
                Authentification::register($mail, $mdp, $nom, $prenom, $noCarte);
                header("location:?action=Payement");
            } catch (AlreadyRegisteredEmailException|BadPasswordException|NotAnEmailException $e) {
                $html = $e->getMessage();
            }


        }
        return $html;
    }
}