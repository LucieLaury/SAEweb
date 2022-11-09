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

        //Update de la liste en cours
        session_start();
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $user->updateListeEnCours($episode);
        $user = serialize($user);
        $_SESSION['user'] = $user;

        //Video
        $html = "";
        $html.="<video controls>";
        $html.="<source src=videos/";
        //Ici le fichier de l'episode
        $html.=$episode->file;
        $html.="type=\"video/mp4\">";
        $html.="</video>";

        //Titre, resume, duree
        $html.="<strong>".$episode->titre."</strong>";

        $html.="<p>".$episode->resume."</p><br>";

        $html.="<p>".$episode->duree."</p><br>";

        return $html;
    }
}