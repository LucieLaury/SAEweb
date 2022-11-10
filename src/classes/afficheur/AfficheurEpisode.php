<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Episode;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class AfficheurEpisode extends Afficheur
{
    /**
     * @throws ProprieteInexistanteException
     */
    public function execute(): string
    {
        //cast en raison que ce get est un string par défaut
        $id = (int) $_GET['id'];
        $episode = Episode::find($id);

        $idS=$episode->idSerie;

        //Update de la liste en cours
        session_start();
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $user->updateListeEnCours($episode);
        $user = serialize($user);
        $_SESSION['user'] = $user;

        //---Génération de l'HTML---//

        //Video
        $html = "";

        $html .= "<div class='max-w-2xl' style='text-align: left; margin-left: 50px;'>";

        //$html .="<div class='mx-4 shadow-2xl rounded-xl w-52 h-72 bg-gray-700 text-center text-white mb-1' style='overflow: auto; '>";

        $html.="<video controls>";
        $html.="<source src=videos/";
        //Ici le fichier de l'episode
        $html.=$episode->file;
        $html.=" type=\"video/mp4\">";
        $html.="</video>";

        //Titre
        $html.="<strong>".$episode->titre."</strong>";

        //Episode
        $html.="<p>"."Description : <br>".$episode->resume."</p><br>";

        //Durée
        $duree = intval(abs($episode->duree / 60));
        $html.="<p>"."Durée : ".$duree." minutes"."</p><br>";

        //Bouton de retour à la série
        $html.="<a href=\"?action=afficher-serie&id=$idS\"><button  class=\"mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-red-500 hover:to-yellow-500\">Retour à la série</button></a>";

        $html.="</div>";

        return $html;
    }
}