<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\catalogue\Serie;

class RenderEpisode
{

    protected Serie $s;

    public function __construct(Serie $s)
    {
        $this->s = $s;
    }

    public function render():string{
        $img = $this->s->__get("img");
        $titre = $this->s->titre;
        $desc = $this->s->descriptif;
        $annee = $this->s->annee;
        $date = $this->s->date;

        $res="<div style='display: flex; flex-direction: row;'>";

            $res.="<img src='$img' width='25%'/>";
            $res.="<div style='text-align: center; margin-left: 30px;'>
                <p><strong>$titre</strong></p>
                <p>genre : </p>
                <p>public visé : </p>
                <p>descriptif : $desc</p>
                <p>année de sortie : $annee</p>
                <p>date d'ajout : $date</p>
            </div>";


        $res.="</div>";



        return $res;

    }

}