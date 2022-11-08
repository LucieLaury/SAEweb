<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\exception\BadPasswordException;

class AfficheurConnexion extends Afficheur
{


    /**
     * @throws BadPasswordException
     */
    public function execute(): string
    {
        if($_SERVER['REQUEST_METHOD'] === "GET") {
            $html = <<<END
                <section id="sec" class="flex flex-col justify-center mt-20 h-full my-auto" >
                <h1 class="text-center text-2xl font-bold">Connexion</h1>
                <form id="connexion" method="post" action=?Signin">
                    <div class="flex flex-col w-80 mx-auto">
                        <label class="ml-1 font-bold">Email :</label>
                        <input type="text" name="mail" class="shadow rounded" placeholder="bernard@mail.com" required>
                        <label class="ml-1 font-bold mt-6">Mot de passe :</label>
                        <input type="password" name="pw"class="shadow rounded" required>
                    </div>
                     <div class="d-flex justify-center">
                        <button type="submit" class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-red-500 hover:to-yellow-500">Se connecter</button>
                    </div>
                    </form>
                    <a href="?action=register"><button  class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-red-500 hover:to-yellow-500">S'enregistrer</button></a>
                    <a href="?action=forgot"><button  class="mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-red-500 hover:to-yellow-500">Mot de passe oublié ?</button></a>
                </section>
                 
                END;
        }
        else {
            $mail = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
            $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
            $passwrd = filter_var($_POST['pw']);
            Authentification::authenticate($mail, $passwrd);
            Authentification::loadProfile($mail);
        }
        return $html;
    }
}
/*
   <form action="?Signin" method="post">
                <label>Identifiant</label>
                <input type="email" name="email" placeholder="bernard@mail.com" required>
                <br>
                <label>Password</label>
                <input type="password" name="password" required><br>
                <button type="submit">Connexion</button>
            </form>
            <a href="?register">S'enregistrer</a>
            <a href="?forgot">Mot de passe oublié ?</a>


*/