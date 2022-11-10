<?php

namespace iutnc\netVOD\afficheur;

use iutnc\netVOD\db\ConnectionFactory;

class AfficheurInformation extends Afficheur
{
    private $db;

    public function __construct()
    {
        $this->db = ConnectionFactory::makeConnection();
    }

    public function execute(): string
    {
        session_start();
        $res = "<div class='flex flex-row justify-around'>";
        $res .= $this->afficherInfoActuelles();
        $res .= $this->afficherBoutonsModification();
        $res .= "</div>";
        return $res;
    }

    private function afficherInfoActuelles(): string
    {
        $user = $_SESSION['user'];
        $user = unserialize($user);
        $mail = $user->__get('email');
        $querry = $this->db->prepare("select * from utilisateur where email = ?;");
        $querry->execute(array($mail));
        $data = $querry->fetch(\PDO::FETCH_ASSOC);
        $res = "<div class='flex flex-col'><div>vos informations actuelles : </div>";
        $res.="<div class=' my-3 mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2'>";
        $res .= "email : {$data['email']}";
        $res.="</div>";
        $res.="<div class=' my-3 mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2'>";
        $res .= "nom : {$data['nom']}";
        $res.="</div>";
        $res.="<div class=' my-3 mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2'>";
        $res .= "prenom : {$data['prenom']}";
        $res.="</div>";
        $carte = "";
        for ($j = 0; $j < 4; $j++) {
            $carte .= $data['noCarte'][$j];
        }
        $res .= "<div class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 my-3'>$carte **** **** ****</div></div>";

        return $res;
    }

    private function afficherBoutonsModification(): string
    {
        $res = "<div style='flex-direction: column'>";
        $res .= "<a href='?action=changer-Information&mod=Pass'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Changer mot de passe</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Mail'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Changer email</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Nom'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Changer de nom</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Prenom'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Changer de prénom</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Carte'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 mt-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>changer de carte</button></a>";
        $res .= "</div>";
        return $res;
    }
}