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
        //session_start();
        $res = "<div style='flex-direction: row'>";
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
        $res = "<div style='flex-direction: column'>vos informations actuelles :";
        $res .= "email : {$data['email']}";
        $res .= "nom : {$data['nom']}";
        $res .= "prenom : {$data['prenom']}";
        $carte = "";
        for ($j = 0; $j < 4; $j++) {
            $carte .= $data['noCarte'][$j];
        }
        $res .= "$carte **** **** ****</div>";

        return $res;
    }

    private function afficherBoutonsModification(): string
    {
        $res = "<div style='flex-direction: column'>";
        $res .= "<a href='?action=changer-Information&mod=Pass'><button>Changer mot de passe</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Mail'><button>Changer email</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Nom'><button>Changer de nom</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Prenom'><button>Changer de prénom</button></a>";
        $res .= "<a href='?action=changer-Information&mod=Carte'><button>changer de carte</button></a>";
        $res .= "</div>";
        return $res;
    }
}