<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Catalogue;
use iutnc\netVOD\catalogue\Serie;
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
            $res= $this->affichageFormulaire();
            $res .= $this->affichageGlo();
        } else if ($this->http_method =="POST"){
            $res = $this->afficherRecherche();
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

    public function affichageFormulaire() : string {
        $html = "<div style='width = 100%; text-align: center; margin-bottom: 40px'>";
        $html .= "<form method='post' action='?action=afficher-catalogue'>";
        $html .= "<input type='search' name='rech' placeholder='rechercher un film' style='width: 50%; margin-right: 10px'/>";
        $html .= "<input type='submit' name='submit' value='Envoyer' />";
        $html .= "</form>";
        $html .= "</div>";


        return $html;
    }


    public function afficherRecherche():string{

        $res = "<div style='display: flex; flex-direction: row; justify-content: space-around'>";
        //séparation des mots de la methode post
        $tab = explode(' ', $_POST['rech']);
        //tableau qui répertorie les titres des series qui vont etre affichees
        $seriesAffichees = array();

        for($i=0; $i<count($tab); $i++){
            $mot = $tab[$i];
            $tab1 = $this->requeteRech($mot, "titre", $seriesAffichees);
            $res .= $tab1[1];
            $tab2 = $this->requeteRech($mot, "descriptif", $tab1[0]);
            $res .= $tab2[1];
        }
        $res .="</div>";
        return $res;
    }

    public function requeteRech(string $mot, string $retour, array $serieAff):array{
        $db = ConnectionFactory::makeConnection();
        $req = $db->query("SELECT titre from serie where $retour like '%".$mot."%' ");
        $req->execute();

        $res ="";
        while ($row = $req->fetch()){

            if (!isset($serieAff[$row['titre']])){
                $serie = Serie::find($row['titre']);
                $re = new RenderSerie($serie);
                $res .= $re->render();
                $serieAff[$row['titre']]=$serie;
            }
        }
        $tab = [];
        $tab[0] = $serieAff;
        $tab[1] = $res;
        return $tab;
    }

}