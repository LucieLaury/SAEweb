<?php

namespace iutnc\netVOD;


class Dispatcher
{
    /**
     * Méthode qui effectue l'action adaptée
     * @return void
     */
    public function run():void{
        $action = $_GET['action'];
        $res = "";
        switch ($action){
                //Cas de l'affichage détaillé d'un épisode
            case "afficher-episode":
                $act = new AfficheurEpisode();
                $res = $act->execute();
                break;
                //Cas de l'affichage d'une série contenant des épisodes
            case "afficher-serie":
                $act = new AfficheurSerie();
                $res = $act->execute();
                break;
                //Cas de l'affichage des commentaires d'une série
            case "afficher-commentaires":
                $act = new AfficheurCommentaires();
                $res = $act->execute();
                break;
                //Cas de l'affichage d'un catalogue
            case "afficher-catalogue":
                $act = new AfficheurCatalogue();
                $res = $act->execute();
                break;
                //cas de la connexion d'un utilisateur
            case "signin":
                $act = new AfficheurConnexion();
                $res = $act->execute();
                break;
                //default : a voir s'il affiche une autre page de bienvenue un peu plus jolie
            default :
                print "Bienvenue ! <br>";
        }
        $this->renderPage($res);
    }

    /**
     * Méthode qui effectue un rendu de la page
     * @param String $html
     * @return void, sachant que le rendu est écrit sur la page
     */
    private function renderPage(String $html) : void {
        $res = <<<END
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>NetVOD</title>
        </head>
        <body>
        END;

        $res .= $html;

        $res .= "</body>
        </html>";
        echo $res;
    }
}