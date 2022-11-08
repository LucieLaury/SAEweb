<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\render\RenderEpisode;
use iutnc\netVOD\render\RenderSerie;

require_once 'Afficheur.php';



class AfficheurSerie extends Afficheur
{

    private Serie $serie;
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = ConnectionFactory::makeConnection();
        $id = $_GET['id'];
        $titre = $this->getTitre($id, $this->db);
        $this->serie = Serie::find($titre);
    }

    public function getTitre(int $id, \PDO $bd): string{

        $req = $this->db->prepare("SELECT titre from serie where id = :id");
        $req->bindParam(":id", $id);
        $req->execute();
        $row = $req->fetch();
        $titre = $row['titre'];
        return $titre;
    }

    public function execute(): string
    {
        $res="";
        $res.=$this->affichageSerie();
        $episodes = $this->serie->__get('episodes');
        for ($i = 0; $i<$this->serie->__get("nbEpisodes"); $i++){
            $episodeC = $episodes[$i];
            $re = new RenderEpisode($episodeC);
            $res .= $re->render();
        }


        return $res;
    }

    public function affichageSerie(): string{
        $img = $this->serie->__get("img");
        $titre = $this->serie->titre;
        $desc = $this->serie->descriptif;
        $annee = $this->serie->annee;
        $date = $this->serie->date;

        $res="<div style='display: flex; flex-direction: row; margin-bottom: 50px;'>";

        $res.="<img src='$img' width='25%'/>";
        $res.="<div style='text-align: center; margin-left: 30px;'>
                <p><strong>$titre</strong></p>
                <p>genre : </p>
                <p>public visé : </p>
                <p>descriptif : $desc</p>
                <p>année de sortie : $annee</p>
                <p>date d'ajout : $date</p>
            </div>";


        $res.="</div>";
        return $res;
    }



    ////partie Nathanael:

}