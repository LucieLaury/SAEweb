<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\render\RenderEpisode;
use iutnc\netVOD\render\RenderSerie;
use iutnc\netVOD\user\User;

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
        $res = "";
        $res .= $this->affichageSerie();
        $episodes = $this->serie->__get('episodes');
        for ($i = 0; $i < $this->serie->__get("nbEpisodes"); $i++) {
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
        $id = $this->serie->id;

        if ($_SERVER['REQUEST_METHOD'] === "POST"){
            if(isset($_POST['BLike'])){
                $user = $_SESSION['user'];
                $user = unserialize($user);
                $user->LikeOuPas($this->serie->id);
            }
        }

        $res = "<div style='display: flex; flex-direction: row; margin-bottom: 50px;'>";

        $res .= "<img src='$img' class='min-w-m'  width='33%'/>";
        $res .= "<script src='javascript/register.js'></script>";
        $res .= "<div class='max-w-2xl' style='text-align: center; margin-left: 30px;'>
                            <div class='grid grid-cols-3 '>
                                <p class='col-start-2'><strong>$titre</strong></p>
                                
                                
                                <form action='' method='post'>
                                <button name='BLike' type='submit'>";


                            $res .= $this->fav();
                            $res .= "</button></form>
                            
                             </div>
                            <p>Genre : </p>
                            <p>Public visé : </p>
                            <p>Descriptif : $desc</p>
                            <p>Année de sortie : $annee</p>
                            <p>Date d'ajout : $date</p>
                      </div>";


        $res .= $this->noteEtComms();
        $res .= "</div>";
        return $res;
    }

    public function fav(): string
    {
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $favoris = $user->listeType(User::FAVORIS);

        if (!in_array($this->serie, $favoris)) return "<img src='src/Styles/img/starBorder.png' width='20%'>";
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
            $res .= "Note moyenne de la série : $note";
        }
        else {
            $res .= "Cette série n'a encore jamais été notée, soyez le premier !";
        }
        if(!$alreadyNoted || $this->updatingNote){
            $res.= "<form method='post'>".
                    "<input type='number' class='shadow rounded' name='note' placeholder='note /5' max='5' min='0'>".
                    "<button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' name='Bnote' type='submit'>Noter</button>";
        }
        else{
            $res.= "<form method='post'>".
                "<button name='BUpdateNote' type='submit'>Changer ma note</button></br>"
                ."<button name='BDeleteNote' type='submit'>Supprimer ma note</button>";
        }
        $res .= "</form><a href=?action=afficher-commentaires&id=$id style='margin-top: 30px'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Accéder aux commentaires</button></a></div>";
        return $res;
    }
}