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
        $html .= "<input type='search' name='rech' placeholder='rechercher un film' style='width: 50%; margin-right: 10px'/>".
            "<input type='submit' name='submit' value='Envoyer' />";
        ;
        $options = array("titre","récent","nombre d'épisodes","meilleures notes");
        $value = array("titre", "date", "nbEpisodes", "note");
        $html .= "<form method='post'>".
            "<select name='tri' style='justify-self: left'>".
            "<option value='none' selected hidden> trier par </option>";
        foreach (array_keys($options) as $key) {
            $html .= "<option value=$value[$key]> $options[$key]</option>";
        }
        $html.="</select></form></div>";
        /*
        $html .= "<input type='submit' name='submit' value='Envoyer' /><br>";
        $html .= "<label>Trie par : </label> <input type='radio' name='trie' value='titre'/><label style='margin-right:40px'>titre</label>";
        $html .= "<input type='radio' name='trie' value='date'/> <label style='margin-right:40px'>Date</label>";
        $html .= "<input type='radio' name='trie' value='nbEpisodes'/><label style='margin-right:40px'>nombre d'Episode</label>";
        $html .= "</form>";
        $html .= "</div>";*/

        return $html;
    }


    public function afficherRecherche():string{
        print $_POST['tri'];
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
            $res .= $this->trierFilms('titre', $tab2);
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
    public function trierFilms(string $trie='titre', array $serieAff):string{
        $i = 0;
        foreach ($serieAff as $val1){
            $min = $val1->__get($trie);
            $j=0;
            foreach ($serieAff as $val2){
                if ($j>$i){
                    $valtri = $val2->__get($trie);
                    if ($valtri < $min) {
                        $min = $valtri;
                        $jbis = $j;
                    }
                }
                $j++;
            }
            if ($min < $val1){
                $serieAff[$i]=$min;
                $serieAff[$jbis]=$val1;
            }
            $i++;
        }

        $res="";

        //ksort($serieAff);

        foreach ($serieAff as $serie) {
            $re = new RenderSerie($serie);
            $res .= $re->render();
        }
        return $res;
    }





}