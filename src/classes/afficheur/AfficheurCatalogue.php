<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Catalogue;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\render\RenderSerie;

class AfficheurCatalogue extends Afficheur
{

    private Catalogue $catalogue;

    public function __construct()
    {
        parent::__construct();
        $this->catalogue = Catalogue::addSerieDB();
    }

    public function execute(): string
    {
        $res="";
        if ($this->http_method == "GET"){
            $res = $this->affichageGlo();
        }

        return $res;

    }

    public function affichageGlo():string{
        $html = "<div style='display: flex;justify-content: space-around; flex-direction: row; flex-wrap: wrap'>";
        $series = $this->catalogue->__get("series");
        for ($i = 0; $i<$this->catalogue->__get("nbSeries"); $i++){
            $serieC = $series[$i];
            $titre = $serieC->__get("titre");
            $re = new RenderSerie($serieC);
            $html .= $re->render();

        }

        $html .="</div>";

        return $html;
    }
}