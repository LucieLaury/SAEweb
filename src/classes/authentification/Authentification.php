<?php

class Authentification
{
    public static function register(string $email, string $password) : void {}

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

    public static function authenticate(string $email, string $passwd2check): void {}


}