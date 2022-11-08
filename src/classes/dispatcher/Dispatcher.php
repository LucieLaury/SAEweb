<?php

namespace iutnc\netVOD\dispatcher;
use iutnc\netVOD\afficheur\AfficheurCatalogue;
use iutnc\netVOD\afficheur\AfficheurConnexion;

class Dispatcher
{
    private string $action;

    /**
     * @param string $action
     */public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * Méthode qui effectue l'action adaptée
     * @return void
     */
    public function run():void{
        $res = "";
        switch ($this->action){
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
            case "accueil-utilisateur":
                $act = new AfficheurUtilisateur();
                $res = $act->execute();
                break;
                //default : a voir s'il affiche une autre page de bienvenue un peu plus jolie
            default :
                $act = new AfficheurConnexion();
                $res = $act->execute();
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
            <link rel="stylesheet" href="src/Styles/CSS/tailwind.css"/>
            <link rel="stylesheet" href="src/Styles/CSS/Catalogue.css"/>
        </head>
        <body class="">
        <header class=" text-gray-300 bg-gray-800 flex flex-row justify-between py-8 shadow-2xl mb-4 "  ">
        <div class="  mx-8 ">
            <a href="?Acceuil-utilisateur"><button class="rounded-2xl hover:bg-gray-300 hover:text-gray-800 m-2 p-1 px-3 h-full ">Accueil</button></a>
        </div>
        <div class="flex flex-row mx-8 ">
            <div class="justify-start mx-8 "> 
            <a href="?register"><button class="rounded-2xl hover:bg-gray-300 hover:text-gray-800 m-2 p-1 px-3 h-full ">test</button></a>

            <a href="?register"><button class="rounded-2xl hover:bg-gray-300 hover:text-gray-800 m-2 p-1 px-3 h-full ">test1</button></a>
            <a href="?register"><button class="rounded-2xl hover:bg-gray-300 hover:text-gray-800 m-2 p-1 px-3 h-full ">test2</button></a>
            </div>
            <div  class=""> 
               <a href="?register"><button class="rounded-2xl hover:bg-gray-300 hover:text-gray-800 m-2 p-1 px-3 h-full ">Se déconnecter</button></a>
            </div>
        </div>
        </header>
        END;

        $res .= $html;

        $res .= "</body>
        </html>";
        echo $res;
    }

}