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
                    <label>Nouveau mdp</label>
                    <input type="password" name="firstPWD" id="firstPWD" required>
                    <input type="password" name="secondPWD" id="secondPWD" onchange="verifPWD()" required>
                    <button type="submit" disabled>Valider</button> 
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