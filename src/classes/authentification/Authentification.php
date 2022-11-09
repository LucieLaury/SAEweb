<?php

namespace iutnc\netVOD\authentification;
use Exception;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\AlreadyRegisteredEmailException;
use iutnc\netVOD\exception\BadPasswordException;
use iutnc\netVOD\exception\CardNotExistingException;
use iutnc\netVOD\exception\InvalidUserException;
use iutnc\netVOD\exception\NotAnEmailException;
use iutnc\netVOD\user\User;
use PDO;

class Authentification
{
    /**
     * @throws BadPasswordException
     * @throws AlreadyRegisteredEmailException
     * @throws NotAnEmailException
     * @throws CardNotExistingException
     */
    public static function register(string $email, string $password, string $nom, string $prenom, string $noCarte) : void {
        $bd = ConnectionFactory::makeConnection();
        // Verify strength of password
        if(strlen($noCarte) !== 16) throw new CardNotExistingException("La longueur ne correspond pas a un code valide");
        if(!self::checkPassStrength($password, 1)) throw new BadPasswordException("Le mot de passe ne correspond pas à nos critères");
        // Verify if email already exist
        $query = $bd->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $query->bindParam(1, $email);
        $query->execute();
        if($query->rowCount() > 0) throw new AlreadyRegisteredEmailException("Cet email est déjà enregistré");
        $query->closeCursor();
        // Sanitize the email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new NotAnEmailException("Ce n'est un email");
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        // Hash the password
        $passwordHash =password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        // Insert the user in database
        $insert = $bd->prepare("INSERT INTO utilisateur (email, nom, prenom, noCarte, pwd) value (:email, :nom, :prenom, :noCarte, :pwd)");
        $insert->bindParam("email", $email);
        $insert->bindParam("pwd", $passwordHash);
        $insert->bindParam("nom", $nom);
        $insert->bindParam("prenom", $prenom);
        $insert->bindParam("noCarte", $noCarte);
        $insert->execute();
        $query->closeCursor();
        $insert->closeCursor();
        try {
            self::generateActivationToken($email);
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    public static function checkPassStrength(string $password, int $minLength) : bool {
        $length = strlen($password) > $minLength;
        $digit = true; // preg_match("#\d#", $password);
        $special = true; // preg_match("#\W#", $password);
        $lower = true; // preg_match("#[a-z]#", $password);
        $upper = true; // preg_match("#[A-Z]]#", $password);
        return ($length && $digit && $special && $lower && $upper);
    }

    public static function loadProfile(string $email) : void {
        $bd = ConnectionFactory::makeConnection();
        $query = $bd->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $query->bindParam(1, $email);
        $query->execute();
        $data = $query->fetch();
        session_start();
        $user = new User($email, $data['nom'], $data['prenom'], $data['noCarte']);
        $_SESSION['user'] = serialize($user);
    }

    /**
     * @throws InvalidUserException
     * @throws Exception
     */
    public static function generateActivationToken(string $email) : void {
        $bd = ConnectionFactory::makeConnection();
        $query = $bd->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $query->bindParam(1, $email); $query->execute();
        if($query->rowCount() === 0) throw new InvalidUserException("Cet email ne correspond a aucun compte");
        $token = bin2hex(random_bytes(512));
        $time = date("d/m/Y H:i:s",time() + 60 * 15);
        $query = $bd->prepare("UPDATE utilisateur SET token = :token ,timestemp = str_to_date(:time, '%d/%m/%Y %T') WHERE email = :mail");
        $query->bindParam("token", $token);
        $query->bindParam("time", $time);
        $query->bindParam("mail", $email);
        $query->execute();
        header("location:?action=activation&token=$token");
    }

    public static function activate(string $token, string $email) : void {
        $db = ConnectionFactory::makeConnection();
        $query = $db->prepare("SELECT token, timestemp WHERE email = ?");
        $query->bindParam($email);
        $query->execute();
        $data = $query->fetch();
        if($token === $data['token'] and date("d/m/Y H:i:m", time()) < $data['timestemp'] )  {
            $query = $db->prepare("UPDATE utilisateur SET activate = true, token = null WHERE email = ?");
            $query->bindParam(1, $email);
            $query->execute();
        }
    }

    /**
     * @throws BadPasswordException
     */
    public static function authenticate(string $email, string $passwd2check): void {
        $bd = ConnectionFactory::makeConnection();
        $query = $bd->prepare("select * from utilisateur where email = ? ");
        $query->bindParam(1, $email);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $hash = $data['pwd'];
        if(!password_verify($passwd2check, $hash)) throw new BadPasswordException("Le mot de passe ou l'identifiant saisi est incorrect");
    }


}