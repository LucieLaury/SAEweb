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
        $html .= "<input type='submit' name='submit' value='Envoyer' /><br>";
        $html .= "<label>Trie par : </label> <input type='radio' name='trie' value='titre'/><label style='margin-right:40px'>titre</label>";
        $html .= "<input type='radio' name='trie' value='date'/> <label style='margin-right:40px'>Date</label>";
        $html .= "<input type='radio' name='trie' value='nbEpisodes'/><label style='margin-right:40px'>nombre d'Episode</label>";
        $html .= "</form>";
        $html .= "</div>";


        return $html;
    }


    public function afficherRecherche():string{

        $res = $this->affichageFormulaire();
        $res .= "<div style='display: flex;justify-content: space-around; flex-direction: row; flex-wrap: wrap'>";
        //séparation des mots de la methode post
        $tab = explode(' ', $_POST['rech']);
        //tableau qui répertorie les titres des series qui vont etre affichees
        $seriesAffichees = array();

        for($i=0; $i<count($tab); $i++){
            $mot = $tab[$i];
            $tab1 = $this->requeteRech($mot, "titre", $seriesAffichees);
            $tab2 = $this->requeteRech($mot, "descriptif", $tab1);
            $res .= $this->trierFilms($_POST['trie'], $tab2);
        }
        $res .="</div>";
        return $res;
    }

    public function requeteRech(string $mot, string $retour, array $serieAff):array{
        $db = ConnectionFactory::makeConnection();
        $req = $db->query("SELECT titre from serie where $retour like '%".$mot."%' ");
        $req->execute();

        while ($row = $req->fetch()){
            if (!isset($serieAff[$row['titre']])){
                $serie = Serie::find($row['titre']);
                $serieAff[$row['titre']]=$serie;
            }
        }
        return $serieAff;
    }

    /**
     * methode qui trie le tableau des séries à afficher dans l'ordre du trie voulu. Soit sur le titre
     * soit sur la date ou le nombre d'épisode de la série
     * @param string $trie qui prend soit les valeurs "titre", "date", "nbEpisodes"
     * @param array $serieAff le tableau des séries qui vont être affichées
     * @return string l'html d'affichage des séries.
     */
    public function trierFilms(string $trie="titre", array $serieAff):string{
        $tabtrie=[];

        for ($i=1; $i<count($serieAff)-1;$i++){
            $val1 = $serieAff[$i]->__get($trie);
            $min = $val1;
            for ($j=$i+1;$j<count($serieAff);$j++){
                $valeur = $serieAff[$j]->__get($trie);
                if ($valeur < $min){
                    $min = $valeur;
                }
            }
            if ($min > $val1)
        }

        $res="";

        ksort($serieAff);

        foreach ($serieAff as $serie) {
            $re = new RenderSerie($serie);
            $res .= $re->render();
        }
        return $res;
    }





}