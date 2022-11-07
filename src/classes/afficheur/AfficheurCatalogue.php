<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Catalogue;
use iutnc\netVOD\db\ConnectionFactory;

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
        $html = "<div style='display: flex;justify-content: space-around; flex-direction: row; text-align: center;
flex'>";
        $series = $this->catalogue->__get("series");
        for ($i = 0; $i<$this->catalogue->__get("nbSeries"); $i++){
            $nomDiv = "serie_".$i;
            $serieC = $series[$i];
            $titre = $serieC->__get('titre');
            $description = $serieC->__get('descriptif');
            $dates = $serieC->__get('annee') . "    " . $serieC->__get('date');
            $nbEpisodes = $serieC->__get('nbEpisodes');
            $html .= "<div id=$nomDiv style='width: 150px; height: 200px; background-color: darkslategray;
color: white; text-align: center; overflow: auto'>
<p><strong>$titre</strong></p>
<p>$description</p>
<p>$dates</p>
<p>$nbEpisodes</p>
            </div>";
        }

        $html .="</div>";

        return $html;
    }

}