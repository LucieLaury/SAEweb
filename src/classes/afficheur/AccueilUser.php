<?php

namespace iutnc\netVOD\afficheur;
use iutnc\netVOD\render\RenderSerie;
use MongoDB\Driver\Session;
use iutnc\netVOD\user\User;

class AccueilUser extends Afficheur
{

    public function execute(): string
    {
        session_start();

        if ($this->http_method == "GET") {
            if(!(isset($_SESSION['user'])))header("location:?action=");
            $res = "<a href='?action=Information'><button class='mx-auto block shadow rounded-2xl p-1 px-3 font-medium block mx-2 my-5 bg-gradient-to-r from-green-400 to-blue-500 text-white hover:from-blue-500 hover:to-green-400'>Informations</button></a>
";
            $res .= "<label class=' mx-auto block shadow text-left pl-5 underline text-2xl bg-blue-500 rounded-2xl'>Mes Favoris</label>";
            $res .= "<div class='flex flex-row'>";
            $user = $_SESSION['user'];
            $user = unserialize($user);

            $list =  $user->listeType(User::FAVORIS);
            foreach ($list as $value) {
                $r = new RenderSerie($value);
                $res .= $r->render();
            }
            $res.="</div>";


            $res .= "<label class=' mx-auto block shadow text-left pl-5 underline text-2xl bg-blue-500 rounded-2xl'>En cours</label>";

            $res .= "<div class='flex flex-row'>";
            $listEnCours =  $user->listeType(User::ENCOURS);
            foreach ($listEnCours as $value) {
                $r = new RenderSerie($value);
                $res .= $r->render();
            }
            $res.="</div>";

            $res .= "<label class=' mx-auto block shadow text-left pl-5 underline text-2xl bg-blue-500 rounded-2xl'>Déjà visionnées</label>";
            $res .= "<div class='flex flex-row'>";
            $listVisio =  $user->listeType(User::VISIONNER);
            foreach ($listVisio as $value) {
                $r = new RenderSerie($value);
                $res .= $r->render();
            }
            $res.="</div>";
            return $res;
        }
        else
            if ($this->http_method == "POST") {
                $res="";
                return $res;
            }
            return "";
        }

}