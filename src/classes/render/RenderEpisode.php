<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\catalogue\Episode;
use iutnc\netVOD\catalogue\Serie;

class RenderEpisode
{

    protected Episode $e;

    public function __construct(Episode $e)
    {
        $this->e = $e;
    }

    public function render():string{
        $no = $this->e->__get('numero');
        $titre = $this->e->__get('titre');
        $duree = $this->e->__get('duree');

        $res = "<div style='display: flex;justify-content: space-around; flex-direction: row; flex-wrap: wrap'>";
        $res .= "<div class='mx-4 shadow-2xl rounded-xl w-52 h-72 bg-gray-700 text-center text-white mb-1'
style='overflow: auto; '>";
        $res .= "<p><strong>$no</strong></p>";
        $res .= "<p><strong>$titre</strong></p>";
        $res .= "<p>durée : $duree</p>";


        $res .= "</div>";



        return $res;

    }

}