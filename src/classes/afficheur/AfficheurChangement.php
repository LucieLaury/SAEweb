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
        $res = "<div><form style='flex-direction: column' method='post'>";
        $failed = false;
        switch ($_GET['mod']) {
            case 'Pass':
                $res .= "<label>entrez le nouveau mot de passe</label>";
                $res .= "<input type='password' placeholder='nouveau mot de passe' name='nPass'>";
                $res .= "<label>confirmez le nouveau mot de passe</label>";
                $res .= "<input type='password' placeholder='nouveau mot de passe' name='nPass2'>";
                break;
            case 'Mail' :
                $res .= "<label>entrez la nouvelle adresse</label>";
                $res .= "<input type='text' placeholder='nouvel adresse' name='nAdr'>";
                break;
            case 'Nom' :
                $res .= "<label>entrez le nouveau nom</label>";
                $res .= "<input type='text' placeholder='nouveau nom' name='nNom'>";
                break;
            case 'Prenom':
                $res .= "<label>entrez le nouveau prénom</label>";
                $res .= "<input type='text' placeholder='nouveau prénom' name='nPre'>";
                break;
            case 'Carte':
                $res .= "<label>entrez la nouvelle carte</label>";
                $res .= "<input type='text' placeholder='nouvelle carte' minlength='16' maxlength='20' name='nCar'>";
                break;
            case 'Suppr':
                break;
            default :
                $res .= "une erreur est survenue <a href='?action=Information'>retour aux informations</a>";
                $failed = true;
                break;
        }
        if (!$failed) {
            $res .= "<label>entrez le mot de passe actuel pour confirmer</label>";
            $res .= "<input type='password' placeholder='mot de passe' name='pass'>";
        }
        $res .= "<button type='submit'>valider</button></form></div>";
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
            $res = "<div>mot de passe incorrect <a href='?action=Information'>retour aux informations</a></div>";
        }
        if (!isset($res)) {
            $res = "<div>";
            switch ($_GET['mod']) {
                case 'Pass':
                    if ($_POST['nPass2'] == $_POST['nPass']) if (Authentification::checkPassStrength($_POST['nPass'], 10)) {
                        $hashedPass = password_hash($_POST['nPass'], PASSWORD_DEFAULT, ['cost' => 12]);
                        $query = $bd->prepare("update utilisateur set pwd = ? where email = ?");
                        $query->execute([$hashedPass,$mail]);
                        $res .="Mot de passe modifié avec succes";
                    } else $res .= "Mot de passe trop faible";
                    else $res .= "les mots de passes ne correspondent pas";
                    break;
                case 'Mail':
                    $newMail = filter_var($_POST['nAdr'], FILTER_VALIDATE_EMAIL);
                    foreach (['utilisateur','episodesVisionnes','feedback'] as $table){
                        $req = "update $table set email = ? where email = ?";
                        $query = $bd->prepare($req);
                        $query->execute([$newMail,$mail]);
                    }
                    $user->setMail($newMail);
                    $_SESSION['user'] = serialize($user);
                    $res .= "Mail modifié avec succes";
                    break;
                case 'Nom' :
                    $query = $bd->prepare("update utilisateur set nom = ? where email = ?");
                    $newName = filter_var($_POST['nNom'], FILTER_SANITIZE_STRING);
                    $query->execute([$newName,$mail]);
                    $res .= "nom modifié avec succes";
                    break;
                case 'Prenom':
                    $query = $bd->prepare("update utilisateur set prenom = ? where email = ?");
                    $newName = filter_var($_POST['nPre'], FILTER_SANITIZE_STRING);
                    $query->execute([$newName,$mail]);
                    $res .= "Prénom modifié avec succes";
                    break;
                case 'Carte':
                    $carte = filter_var($_POST['nCar'], FILTER_VALIDATE_INT);
                    if (strlen($carte) == 16) {
                        $query = $bd->prepare("update utilisateur set noCarte = ? where email = ?");
                        $query->execute([$carte,$mail]);
                        $res .= "Carte modifiée avec succes";
                    } else $res.= "carte invalide";
                    break;
                case 'Suppr': foreach (['episodesVisionnes','feedback','utilisateur'] as $table){
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
        }
        $res .= "<a href='?action=Information'>retour aux informations</a></div>";
        return $res;
    }
}