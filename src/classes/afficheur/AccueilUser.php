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
            $res = "<label class=' mx-auto block shadow text-left pl-5 underline text-2xl bg-blue-500 rounded-2xl'>Mes Favoris</label>";
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