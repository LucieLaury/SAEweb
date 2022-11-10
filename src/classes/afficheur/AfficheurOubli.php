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
                    <label>Entrez votre email</label>
                    <input type="email" name="email" required>
                    <button type="submit">Valider</button> 
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