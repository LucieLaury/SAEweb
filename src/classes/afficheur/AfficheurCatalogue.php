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
        session_start();
        $res="";
        if ($this->http_method == "GET"){
            if(!(isset($_SESSION['user'])))header("location:?action=");

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
            "<input class=' text-gray-300 rounded-2xl p-1 px-3 mr-5 bg-gray-800' type='submit' name='submit' value='Envoyer' />";
        ;
        $options = array("titre","récent","nombre d'épisodes","meilleures notes");
        $value = array("titre", "date", "nbEpisodes", "note");
        $html .= "<form method='post'>".
            "<select name='tri' style='justify-self: left'>".
            "<option value='titre' selected hidden> trier par </option>";
        foreach (array_keys($options) as $key) {
            $html .= "<option value=$value[$key]> $options[$key]</option>";
        }
        $html.="</select></form></div>";

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
            $seriesAffichees = $tab2;
        }
        $res .= $this->trierFilms($_POST['tri'], $tab2);
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
    public function trierFilms(string $trie, array $serieAff):string{
        $tabtrie=[];
        $tabkeys = array_keys($serieAff);
        $i=0;
        $indextrie = 0;
        while (count($tabkeys)>0){
            if(!array_key_exists($i, $tabkeys)){
                $i=0;
            } else {
                $titre1 = $tabkeys[0];
                $jbis = 0;
                $j = 0;
                $serie1 = Serie::find($titre1);
                $serieMin = $serie1;
                $valueMin = $serieMin->__get($trie);

                for ($j; $j < count($tabkeys); $j++) {
                    if(array_key_exists($j, $tabkeys)) {
                        $titre2 = $tabkeys[$j];
                        $serie2 = Serie::find($titre2);
                        $value2 = $serie2->__get($trie);
                        if (($value2 <= $valueMin && $trie != 'note') ||($value2 >= $valueMin && $trie == 'note')) {
                            $serieMin = $serie2;
                            $valueMin = $value2;
                            $jbis = $j;
                        }
                    }
                }
                $tabtrie[$indextrie] = $serieMin->__get('titre');
                unset($tabkeys[$jbis]);
                $i++;
                $indextrie++;
                $tabkeys = $this->refonteTabKey($tabkeys);
            }
        }

        $res="";
        foreach ($tabtrie as $titre) {
            $serie = Serie::find($titre);
            $re = new RenderSerie($serie);
            $res .= $re->render();

        }
        return $res;
    }

    public function refonteTabKey(array $tabkey): array{
        $newtab = [];
        $i = 0;
        foreach ($tabkey as $value){
            $newtab[$i] = $value;
            $i++;
        }
        return $newtab;
    }
}