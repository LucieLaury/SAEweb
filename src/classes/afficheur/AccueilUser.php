<?php

namespace iutnc\netVOD\afficheur;
use iutnc\netVOD\render as render;
use MongoDB\Driver\Session;

class AccueilUser extends Afficheur
{

    public function execute(): string
    {
        session_start();

        if ($this->http_method == "GET") {
            $res = "";
            $user = $_SESSION['user'];
            $user = unserialize($user);
            $list = $user->favoris;
            foreach ($list as $value) {
                $r = new render($value);
                $res .= $r->render();
            }
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