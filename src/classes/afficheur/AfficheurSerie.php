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

        $req = $bd->prepare("SELECT titre from serie where id = :id");
        $req->bindParam(":id", $id);
        $req->execute();
        $row = $req->fetch();
        $titre = $row['titre'];
        return $titre;
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