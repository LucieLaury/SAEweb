<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\render\RenderEpisode;
use iutnc\netVOD\render\RenderSerie;

require_once 'Afficheur.php';



class AfficheurSerie extends Afficheur
{

    private Serie $serie;

    public function __construct()
    {
        parent::__construct();
        $titre = $_GET['titre'];
        $titre = str_replace('%20', ' ', $titre);
        $this->serie = Serie::find($titre);
    }

    public function execute(): string
    {
        $res="";
        $render = new RenderEpisode($this->serie);
        $res .= $render->render();

        return $res;
    }



    ////partie Nathanael:

}