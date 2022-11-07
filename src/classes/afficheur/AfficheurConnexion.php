<?php

namespace iutnc\netVOD\afficheur;

class AfficheurConnexion extends Afficheur
{


    public function execute(): string
    {
        return <<<END
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
        END;

    }
}