<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\catalogue\Serie;

class RenderSerie
{
    protected Serie $s;

    public function __construct(Serie $s)
    {
        $this->s = $s;
    }

    public function render():string{
        $res = "<div><body><p> Nom de la série :";
        $res.=$this->s->titre;
        $res.="</p><br>";
        $res.="<p> Année de sortie :";
        $res.=$this->s->annee;
        $res.="</p><br>";
        $res.="<p> Résumé :";
        $res.=$this->s->descriptif;
        $res.="</p><br>";
        $res.="<p> Nombre d'épisodes :";
        $res.=$this->s->nbEpisode;
        $res.="</p><br>";
        $res.="</body></div>";

        return $res;

    }


}