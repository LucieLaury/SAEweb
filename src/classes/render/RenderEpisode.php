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
        $res="<div style='display: flex; flex-direction: row;'>";

            $res.="<img src='$img' width='25%'/>";
            $res.="<div style='text-align: center'>
                <p><strong>$titre</strong></p>
            </div>";


        $res.="</div>";



        return $res;

    }

}