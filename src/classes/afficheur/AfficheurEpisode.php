<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Episode;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class AfficheurEpisode extends \iutnc\netVOD\Afficheur
{
    /**
     * @throws ProprieteInexistanteException
     */
    public function execute(): string
    {
        $episode = Episode::find($_GET['']);

        $html = "";
        $html.="<video controls>";
        $html.="<source src=videos/";
        //Ici le fichier de l'episode
        $html.=$episode->file;
        $html.="type=\"video/mp4\">";
        $html.="</video>";

        $html.="<strong>".$episode->title."</strong>";

        $html.="<p>".$episode->resume."</p>";

        $html.="<p>".$episode->duree."</p>";

        return $html;
    }
}