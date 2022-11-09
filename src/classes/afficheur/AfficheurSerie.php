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
        $titre = $this->getTitre((int) $id);
        $this->serie = Serie::find($titre);
    }

    public function getTitre(int $id): string{

        $req = $this->db->prepare("SELECT titre from serie where id = :id");
        $req->bindParam(":id", $id);
        $req->execute();
        $row = $req->fetch();
        $titre = $row['titre'];
        return $titre;
    }

    public function execute(): string
    {
        session_start();
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
                <div class='grid grid-cols-3 '>
                <p class='col-start-2'><strong>$titre</strong></p>
                 <button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-red-500 hover:to-yellow-500'>j'aime</button>
                
                </div>
                <p>genre : </p>
                <p>public visé : </p>
                <p>descriptif : $desc</p>
                <p>année de sortie : $annee</p>
                <p>date d'ajout : $date</p>
            </div>";

        $res.= $this->noteEtComms();
        $res.="</div>";
        return $res;
    }



    ////partie Nathanael:
    private function noteEtComms(): string{
        $res = "<div style='display: flex; flex-direction: column; margin-left: 50px;margin-right: 50px'>";
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        $id = $_GET['id'];
        if ($_SERVER['REQUEST_METHOD'] === "POST"){
            if(isset($_POST['Bnote'])){
                $query = $this->db->prepare("select idSerie from feedback where idserie = ? and mail = ?");
                $query->bindParam(1,$id);
                $query->bindParam(2,$mail);
                $query->execute();
                if($data = $query->fetch(\PDO::FETCH_ASSOC))$query = $this->db->prepare("update feedback set note = ? where idserie = ? and mail = ?;");
                else $query = $this->db->prepare("insert into feedback (note,idSerie,mail) values (?,?,?);");
                $query->bindParam(1,$_POST['note']);
                $query->bindParam(2,$id);
                $query->bindParam(3,$mail);
                $query->execute();
            }
        }
        $query = $this->db->prepare("select note, mail from feedback where idserie = ?");
        $query ->bindParam(1,$id);
        $query->execute();
        $tot = 0;
        $div = 0;
        $alreadyNoted = false;
        while ($data = $query->fetch(\PDO::FETCH_ASSOC)){
            if ($data['note'] != null) {
                $tot += $data['note'];
                $div++;
                if ($data['mail'] == $mail) {
                    $alreadyNoted = true;
                }
            }
        }
        if($div!=0){
            $note = $tot / $div;
            $res .= "note moyenne de la serie : $note</br></br>";
        }
        else {
            $res .= "cette serie n'as encore jamais été notées, soyez le premier !";
        }
        if(!$alreadyNoted){
            $res.= "<form method='post'>".
                    "<input type='number' name='note' placeholder='note /5' max='5'>".
                    "<button name='Bnote' type='submit'>noter</button></form>";
        }
        $res .= "<a href=?action=afficher-commentaires&id=$id>acceder aux commentaires</a></div>";
        return $res;
    }
}