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
    private $updatingNote;

    public function __construct()
    {
        parent::__construct();
        $this->db = ConnectionFactory::makeConnection();
        $id = $_GET['id'];
        $titre = $this->getTitre((int) $id);
        $this->serie = Serie::find($titre);
        $this->updatingNote = false;
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

                $res.="<img src='$img' class='min-w-m'  width='33%'/>";
                $res.="<div class='max-w-2xl' style='text-align: center; margin-left: 30px;'>
                            <div class='grid grid-cols-3 '>
                                <p class='col-start-2'><strong>$titre</strong></p>
                                <button>";
                                $res.= $this->fav();
                                $res.="</button>
                            
                             </div>
                            <p>genre : </p>
                            <p>public visé : </p>
                            <p >descriptif : $desc</p>
                            <p>année de sortie : $annee</p>
                            <p>date d'ajout : $date</p>
                      </div>";

                $res.= $this->noteEtComms();
        $res.="</div>";
        return $res;
    }

    public function fav():string{
        $db = ConnectionFactory::makeConnection();
        $id = $this->serie->id;

        $user = $_SESSION['user'];
        $user = unserialize($user);

        $mail = $user->email;



        $req = $db->prepare("SELECT videoPref from feedback
             where idS= :id and email= :mail;");
        $req->bindParam(":id", $id);
        $req->bindParam(":mail", $mail);
        $req->execute();
        $row = $req->fetch();
        $pref =false;
        if($row != null)$pref = $row['videoPref'];
        if($pref == false)return "<img src='src/Styles/img/starBorder.png' width='20%'>";
        else return "<img src='src/Styles/img/star.png' width='20%'>";



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
                $query = $this->db->prepare("select idS from feedback where idS = ? and email = ?");
                $query->bindParam(1,$id);
                $query->bindParam(2,$mail);
                $query->execute();
                if($data = $query->fetch(\PDO::FETCH_ASSOC))$query = $this->db->prepare("update feedback set note = ? where idS = ? and email = ?;");
                else $query = $this->db->prepare("insert into feedback (note,idS,email) values (?,?,?);");
                $query->bindParam(1,$_POST['note']);
                $query->bindParam(2,$id);
                $query->bindParam(3,$mail);
                $query->execute();
                $this->updatingNote = false;
            }
            elseif (isset($_POST['BUpdateNote'])){
                $this->updatingNote = true;
            }
            elseif (isset($_POST['BDeleteNote'])){
                $query = $this->db->prepare("update feedback set note = null where idS = ? and email = ?;");
                $query->bindParam(1,$id);
                $query->bindParam(2,$mail);
                $query->execute();
            }
        }
        $query = $this->db->prepare("select note, email from feedback where idS = ?");
        $query ->bindParam(1,$id);
        $query->execute();
        $tot = 0;
        $div = 0;
        $alreadyNoted = false;
        while ($data = $query->fetch(\PDO::FETCH_ASSOC)){
            if ($data['note'] != null) {
                $tot += $data['note'];
                $div++;
                if ($data['email'] == $mail) {
                    $alreadyNoted = true;
                }
            }
        }
        if($div!=0){
            $note = $tot / $div;
            $res .= "note moyenne de la serie : $note";
        }
        else {
            $res .= "cette serie n'as encore jamais été notées, soyez le premier !";
        }
        if(!$alreadyNoted || $this->updatingNote){
            $res.= "<form method='post'>".
                    "<input type='number' name='note' placeholder='note /5' max='5'>".
                    "<button name='Bnote' type='submit'>noter</button>";
        }
        else{
            $res.= "<form method='post'>".
                "<button name='BUpdateNote' type='submit'>changer ma note</button></br>"
                ."<button name='BDeleteNote' type='submit'>supprimer ma note</button>";
        }
        $res .= "</form><a href=?action=afficher-commentaires&id=$id style='margin-top: 30px'>acceder aux commentaires</a></div>";
        return $res;
    }
}