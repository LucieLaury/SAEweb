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
        session_start();
        // recuperation de l'email de l'utilisateur courant
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        $id = $_GET['id'];
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $query = AfficheurCommentaires::$bd->prepare("select ids from feedback where idS = ? and email = ?");
            $query->bindParam(1, $id);
            $query->bindParam(2, $mail);
            $query->execute();
            if ($data = $query->fetch(PDO::FETCH_ASSOC)) $lineExist = true;
            else $lineExist = false;
            if (isset($_POST['valEdit']) || ($lineExist && isset($_POST['add']))) {
                $query = AfficheurCommentaires::$bd->prepare("update feedback set commentaire = ? where email = ? and idS = ?");
                $comm = filter_var($_POST['comm'], FILTER_SANITIZE_STRING);
                $comm = nl2br($comm);
                $query->bindparam(1, $comm);
                $query->bindparam(2, $mail);
                $query->bindparam(3, $_GET['id']);
                $query->execute();
                $this->edit = false;
            } else if (isset($_POST['rmv'])) {
                if (!$lineExist) $query = AfficheurCommentaires::$bd->prepare("delete from feedback where email = ? and idS = ?");
                else $query = AfficheurCommentaires::$bd->prepare("update feedback set commentaire = null where email = ? and idS = ?");
                $query->bindparam(1, $mail);
                $query->bindparam(2, $_GET['id']);
                $query->execute();
            } elseif (isset($_POST['add'])) {
                if (!$lineExist) $query = AfficheurCommentaires::$bd->prepare("insert into feedback (commentaire,email,idS) values (?,?,?)");
                else $query = AfficheurCommentaires::$bd->prepare("update feedback set commentaire = ? where email = ? and idS = ?");
                $comm = filter_var($_POST['comm'], FILTER_SANITIZE_STRING);
                $comm = nl2br($comm);
                $query->bindparam(1, $comm);
                $query->bindparam(2, $mail);
                $query->bindparam(3, $_GET['id']);
                $query->execute();
            } else $this->edit = true;
        }
        $query = AfficheurCommentaires::$bd->prepare("select email,commentaire from feedback where idS = ?");
        $query->bindParam(1, $_GET['id']);
        $query->execute();
        $alreadyCommented = false;
        $res = "<section class='flex flex-col justify-center mt-20 h-full my-auto' ><div class='justify-center mx-auto'> <label class='justify-center shadow rounded mx-96 mt-8 py-1 px-8  content-center text-center font-bold' >Commentaires de votre serie :</label>";
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            //parcours de tous les commentaires de la serie
            if ($data['commentaire'] != null) {
                $res.="<div class='justify-center shadow rounded mx-96 mt-5 content-center'><p class='text-center'>";
                $res .= $data["email"];
                $res .= "</p></div>";

                if ($mail == $data["email"]) {
                    $alreadyCommented = true;
                    if ($this->edit) {
                        $res .= "<form method=post>"
                            . "<div class='justify-center shadow rounded mx-96 mt-5 content-center'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' name='valEdit' type=submit>valider</button>"
                            . "<textarea class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5' type=text name=comm placeholder='votre commentaire' style='min-height: 100px;min-width: 400px;max-height: 100px;max-width: 400px;'></textarea>"
                            . "</div></form>";
                    } else {
                        $res .= "<form method=post>"
                            . "<div class='justify-center mx-96 mt-5 content-center'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' name='edit' type=submit>modifier commentaire</button><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' name='rmv' type='submit'>supprimer commentaire</button>"
                            . "</div></form>";
                    }
                }
                // on verifie si le commentaire courant a ete poste par l'utilisateur courant
                // cela permet de lui donner l'option de modifier ou supprimer son commentaire ainsi que de l'empecher d'en poser un nouveau
            } else $res .= "";
            $res.="<div class='justify-center shadow rounded mx-96 mt-5 content-center'><p class='text-center'>";
            $res .= $data['commentaire'];
            $res .= "</p></div>";
        }
        if (!$alreadyCommented) {
            //si l'utilisateur n'as pas encore commenter cette serie, on place un champ prevu a cet effet
            $res .= "<form method=post>"
                . "<textarea type=text class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5' name=comm placeholder='votre commentaire' style='min-height: 100px;min-width: 400px;max-height: 100px;max-width: 400px;'></textarea>"
                . "<button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' type=submit name='add'>commenter</button>"
                . "</form>";
        }
        $res .= "<a href=?action=afficher-serie&id=$id><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>retour à la serie</button></a> </div></section>";
        return $res;
    }
}