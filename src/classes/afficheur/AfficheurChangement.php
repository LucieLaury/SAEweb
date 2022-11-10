<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\authentification\Authentification;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\BadPasswordException;

class AfficheurChangement extends Afficheur
{

    public function execute(): string
    {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === "GET") return $this->creerFormulaire();
        else return $this->changerInfos();
    }


    private function creerFormulaire(): string
    {
        $res = "<div><form class='block flex flex-col mx-8 my-3 ' method='post'><div class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5'>";
        $failed = false;
        switch ($_GET['mod']) {
            case 'Pass':
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>entrez le nouveau mot de passe</label>";
                $res .= "<input  class='shadow rounded' type='password' placeholder='nouveau mot de passe' name='nPass'>";
                $res.="</div>";
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>confirmez le nouveau mot de passe</label>";
                $res .= "<input class='shadow rounded' type='password' placeholder='nouveau mot de passe' name='nPass2'>";
                $res.="</div>";
                break;
            case 'Mail' :
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>entrez la nouvelle adresse</label>";
                $res .= "<input class='shadow rounded' type='text' placeholder='nouvel adresse' name='nAdr'>";
                $res.="</div>";
                break;
            case 'Nom' :
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>entrez le nouveau nom</label>";
                $res .= "<input class='shadow rounded' type='text' placeholder='nouveau nom' name='nNom'>";
                $res.="</div>";
                break;
            case 'Prenom':
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>entrez le nouveau prénom</label>";
                $res .= "<input  class='shadow rounded' type='text' placeholder='nouveau prénom' name='nPre'>";
                $res.="</div>";
                break;
            case 'Carte':
                $res.="<div class='my-3'>";
                $res .= "<label class='mr-5 '>entrez la nouvelle carte</label>";
                $res .= "<input  class='shadow rounded' type='text' placeholder='nouvelle carte' minlength='16' maxlength='20' name='nCar'>";
                $res.="</div>";
                break;
                case 'Suppr':
                break;
            default :
                $res.="<div class='my-3'>";
                $res .= "une erreur est survenue <a href='?action=Information'><a href='?action=Information'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>retour aux informations</button></a>";
                $res.="</div>";
                $failed = true;
                break;
        }
        if (!$failed) {
            $res.="<div class='my-3'>";
            $res .= "<label class='mr-5 '>entrez le mot de passe actuel</label>";
            $res .= "<input class='shadow rounded' type='password' placeholder='mot de passe' name='pass'>";
            $res.="</div>";
        }
        $res .= "<button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400' type='submit'>valider</button></div></form></div>";
        return $res;
    }

    private function changerInfos(): string
    {
        $bd = ConnectionFactory::makeConnection();
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        try {
            Authentification::authenticate($mail, $_POST['pass']);
        } catch (BadPasswordException) {
            $res = "<div class=' text-center mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5'> <label class='font-bold'>mot de passe incorrect </label><a href='?action=Information'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>retour aux informations</button></a></div>";
        }
        if (!isset($res)) {
            $res = "<div>";
            switch ($_GET['mod']) {
                case 'Pass':
                    if ($_POST['nPass2'] == $_POST['nPass']) if (Authentification::checkPassStrength($_POST['nPass'], 10)) {
                        $hashedPass = password_hash($_POST['nPass'], PASSWORD_DEFAULT, ['cost' => 12]);
                        $query = $bd->prepare("update utilisateur set pwd = ? where email = ?");
                        $query->execute([$hashedPass, $mail]);
                        $res .= "Mot de passe modifié avec succes";
                    } else $res .= "Mot de passe trop faible";
                    else $res .= "les mots de passes ne correspondent pas";
                    break;
                case 'Mail':
                    $newMail = filter_var($_POST['nAdr'], FILTER_VALIDATE_EMAIL);
                    foreach (['utilisateur', 'episodesVisionnes', 'feedback'] as $table) {
                        $req = "update $table set email = ? where email = ?";
                        $query = $bd->prepare($req);
                        $query->execute([$newMail, $mail]);
                    }
                    $user->setMail($newMail);
                    $_SESSION['user'] = serialize($user);
                    $res .= "Mail modifié avec succes";
                    break;
                case 'Nom' :
                    $query = $bd->prepare("update utilisateur set nom = ? where email = ?");
                    $newName = filter_var($_POST['nNom'], FILTER_SANITIZE_STRING);
                    $query->execute([$newName, $mail]);
                    $res .= "nom modifié avec succes";
                    break;
                case 'Prenom':
                    $query = $bd->prepare("update utilisateur set prenom = ? where email = ?");
                    $newName = filter_var($_POST['nPre'], FILTER_SANITIZE_STRING);
                    $query->execute([$newName, $mail]);
                    $res .= "Prénom modifié avec succes";
                    break;
                case 'Carte':
                    $carte = filter_var($_POST['nCar'], FILTER_VALIDATE_INT);
                    if (strlen($carte) == 16) {
                        $query = $bd->prepare("update utilisateur set noCarte = ? where email = ?");
                        $query->execute([$carte, $mail]);
                        $res .= "Carte modifiée avec succes";
                    } else $res .= "carte invalide";
                    break;
                case 'Suppr':
                    foreach (['episodesVisionnes', 'feedback', 'utilisateur'] as $table) {
                        $req = "delete from $table where email = ?";
                        $query = $bd->prepare($req);
                        $query->execute([$mail]);
                        header('location:Index.php');
                    }
                    unset($_SESSION['user']);
                    break;
                default :
                    $res .= "une erreur est survenue";
                    break;
            }

            $res .= "<a href='?action=Information'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>retour aux informations</button></a></div>";
        }
        return $res;
    }
}