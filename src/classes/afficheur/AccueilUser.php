<?php

namespace iutnc\netVOD\afficheur;
use iutnc\netVOD\render as render;

class AccueilUser extends Afficheur
{

    public function execute(): string
    {
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
                return "";
            }
            return "";
        }

}