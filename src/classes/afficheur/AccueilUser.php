<?php

namespace iutnc\netVOD\afficheur;

class accueilUser extends Afficheur
{

    public function execute(): string
    {
        if ($this->http_method == "GET") {
            $res = "";
            $user = $_SESSION['user'];
            $user = unserialize($user);
            $list = $user->favoris;
            foreach ($list as $value) {
                $res .= $value->render();
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