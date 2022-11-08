<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\db\ConnectionFactory;
use PDO;

class AfficheurCommentaires extends Afficheur
{

    private static $bd;
    private bool $edit;

    public function __construct()
    {
        parent::__construct();
        $this->edit = false;
        AfficheurCommentaires::$bd = ConnectionFactory::makeConnection();
    }


    public function execute(): string
    {
        // recuperation de l'email de l'utilisateur courant
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (isset($_POST['valEdit'])) {
                $query = AfficheurCommentaires::$bd->prepare("update Feedback set txtComm = ? where mail = ? and idSerie = ?");
                $comm = filter_var($_POST['comm'], FILTER_SANITIZE_STRING);
                $comm = nl2br($comm);
                $query->bindparam(1, $comm);
                $query->bindparam(2, $mail);
                $query->bindparam(3, $_GET['idSerie']);
                $query->execute();
                $this->edit = false;
            } else if (isset($_POST['rmv'])) {
                $query = AfficheurCommentaires::$bd->prepare("delete from Feedback where mail = ? and idSerie = ?");
                $query->bindparam(1, $mail);
                $query->bindparam(2, $_GET['idSerie']);
                $query->execute();
            } elseif (isset($_POST['add'])) {
                $query = AfficheurCommentaires::$bd->prepare("insert into Feedback (mail,txtComm,idSerie) values (?,?,?)");
                $comm = filter_var($_POST['comm'], FILTER_SANITIZE_STRING);
                $comm = nl2br($comm);
                $query->bindparam(1, $mail);
                $query->bindparam(2, $comm);
                $query->bindparam(3, $_GET['idSerie']);
                $query->execute();
            } else $this->edit = true;
        }
        $query = AfficheurCommentaires::$bd->prepare("select mail,txtComm from Feedback where idSerie = ?");
        $query->bindParam(1, $_GET['idSerie']);
        $query->execute();
        $alreadyCommented = false;
        $res = "Commentaires de votre serie :</br>";
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            //parcours de tous les commentaires de la serie
            $res .= $data["mail"] . "</br>";
            if ($mail == $data["mail"]) {
                // on verifie si le commentaire courant a ete poste par l'utilisateur courant
                // cela permet de lui donner l'option de modifier ou supprimer son commentaire ainsi que de l'empecher d'en poser un nouveau
                $alreadyCommented = true;
                if ($this->edit) {
                    $res .= "<form method=post>"
                        . "<button name='valEdit' type=submit>valider</button><br>"
                        . "<textarea type=text name=comm placeholder='votre commentaire' style='min-height: 100px;min-width: 400px;max-height: 100px;max-width: 400px;'></textarea>"
                        . "</form>";
                } else {
                    $res .= "<form method=post>"
                        . "<button name='edit' type=submit>modifier commentaire</button><button name='rmv' type='submit'>supprimer commentaire</button><br>"
                        . "</form>";
                }
            } else $res .= "</br>";
            $res .= $data['txtComm'] . "</br>";
            $res .= "</br></br>";
        }
        $id = $_GET['idSerie'];
        if (!$alreadyCommented) {
            //si l'utilisateur n'as pas encore commenter cette serie, on place un champ prevu a cet effet
            $res .= "<form method=post>"
                . "<textarea type=text name=comm placeholder='votre commentaire' style='min-height: 100px;min-width: 400px;max-height: 100px;max-width: 400px;'></textarea>"
                . "</br><button type=submit name='add'>commenter></button>"
                . "</form>";
        }
        $res .= "<button href=?action=afficher-episode?idSerie=$id >retour à la serie</button>";
        return $res;
    }
}