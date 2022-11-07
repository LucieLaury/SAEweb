<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\db\ConnectionFactory;
use PDO;

class AfficheurCommentaires extends Afficheur {

    private static $bd;

    public function __construct(){
        parent::__construct();
        AfficheurCommentaires::$bd = ConnectionFactory::makeConnection();
    }


    public function execute(): string
    {
        // recuperation de l'email de l'utilisateur courant
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            //comme la methode post est utilisée pour supprimer, ajouter ou modifier un commentaire un commence par supprimer l'ancien
            //s'il n'existe pas, on supprime 0 lignes et donc ne pose pas de probleme
            //si on souhaite le modifier, cela permet de partire d'une base saine
            $query = AfficheurCommentaires::$bd->prepare("delete from Feedback where mail = ? and idSerie = ?");
            $query->execute($mail,$_GET['idSerie']);
            if (!isset($_POST['rmv'])){
                //on entre ici uniquement si on souhaite pas supprimer le commentaire
                $query = AfficheurCommentaires::$bd->prepare("insert into Feedback (mail,txtComm,idSerie) values (?,?,?)");
                $comm = filter_var($_POST['comm'],FILTER_SANITIZE_STRING);
                $query->execute($mail,$comm,$_GET['idSerie']);
            }
        }
        $query = AfficheurCommentaires::$bd->prepare("select mail,txtComm from Feedback where idSerie = ?");
        $query->bindParam(1,$_GET['idSerie']);
        $query->execute();
        $alreadyCommented = false;
        $res = "Commentaires de votre serie :</br>";
        while ($data = $this->query->fetch(PDO::FETCH_ASSOC)){
            //parcours de tous les commentaires de la serie
            $res.= $data["mail"]."</br>";
            if ($mail == $data["mail"]) {
                // on verifie si le commentaire courant a ete poste par l'utilisateur courant
                // cela permet de lui donner l'option de modifier ou supprimer son commentaire ainsi que de l'empecher d'en poser un nouveau
                $alreadyCommented = true;
                $t = $data['txtComm']."</br>";
                $res .= "<form method=post>"
                    . "<button name='edit' type=submit>modifier commentaire</button><button name='rmv' type='submit'>supprimer commentaire</button><br>"
                    . "<input name='comm' type='text' content=$t>"
                . "</form>";
            }
            else $res.="</br>".$data['txtComm']."</br>";
        }
        if (!$alreadyCommented) {
            //si l'utilisateur n'as pas encore commenter cette serie, on place un champ prevu a cet effet
            $id = $_GET['idSerie'];
            $res .= "<form method=post>"
                . "<input type=text name=comm placeholder='votre commentaire' required>"
                . "<button type=submit>commenter</button>"
                . "</form>";
        }
        $res.= "<button href=?action=afficher-episode?idSerie=$id text='retour à la serie'>";
        return $res;
    }
}