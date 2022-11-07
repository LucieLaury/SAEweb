<?php

use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\AlreadyRegisteredEmailException;
use iutnc\netVOD\exception\BadPasswordException;

class Authentification
{
    /**
     * @throws BadPasswordException
     * @throws AlreadyRegisteredEmailException
     */
    public static function register(string $email, string $password) : void {
        $bd = ConnectionFactory::makeConnection();
        // Verify strength of password
        if(!self::checkPassStrength($password, 10)) throw new BadPasswordException("Le mot de passe ne correspond pas à nos critères");
        // Verify if email already exist
        $query = $bd->prepare("SELECT * FROM User WHERE mail = ?");
        $query->execute($email);
        if($query->rowCount() > 0) throw new AlreadyRegisteredEmailException("Cet email est déjà enregistré");
        $query->closeCursor();
        // Sanitize the email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        // Hash the password
        $passwordHash =password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        // Insert the user in database
        $insert = $bd->prepare("INSERT INTO User (mail, password) value (?, ?)");
        $insert->execute($email, $passwordHash);
        $query->closeCursor();
        $insert->closeCursor();
    }

    public static function checkPassStrength(string $password, int $minLength) : bool {
        $length = strlen($password) > $minLength;
        $digit = preg_match("#\d#", $password);
        $special = preg_match("#\W#", $password);
        $lower = preg_match("#[a-z]#", $password);
        $upper = preg_match("#[A-Z]]#", $password);
        return ($length && $digit && $special && $lower && $upper);
    }
    public static function checkAccessLevel(int $required): bool {return false;}

    public static function loadProfile(string $email) : void {}

    public static function checkOwner(int $oId, int $plId):bool {return false;}

    public static function generateActivationToken(string $email) : string {return "";}

    public static function activate(string $token) : bool {return false;}

    /**
     * @throws BadPasswordException
     */
    public static function authenticate(string $email, string $passwd2check): void {
        $bd = \iutnc\deefy\db\ConnectionFactory::makeConnection();
        $query = $bd->prepare("select * from User where email = ? ");
        $query->execute($email);
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $hash = $data['passwd'];
        if(!password_verify($passwd2check, $hash)) throw new BadPasswordException("Le mot de passe ou l'identifiant saisi est incorrecte");
    }


}