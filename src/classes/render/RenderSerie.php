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

    public function renderComplet():string{
        $img = $this->s->__get("img");
        $res = "<div style='width: 350px; height: 200px; background-color: darkslategray;
color: white; text-align: center; overflow: auto; margin-bottom: 30px; '><body><p> Nom de la série : ";
        $res.=$this->s->titre;
        $res.="</p>";
        $res.="<img src='$img' width='50%'/>";
        $res.="<p> Année de sortie : ";
        $res.=$this->s->annee;
        $res.="</p>";
        $res.="<p> Résumé : ";
        $res.=$this->s->descriptif;
        $res.="</p>";
        $res.="<p> Nombre d'épisodes : ";
        $res.=$this->s->nbEpisodes;
        $res.="</p>";
        $res.="</body></div>";

        return $res;

    }


    public function render():string{
        $img = $this->s->__get("img");
        $titre = $this->s->__get("titre");
        $res = "<a href='index.php?action=afficher-serie&amp;titre=".$titre."'>";
        $res .= "<div class='mx-4 shadow-2xl rounded-xl w-52 h-72 bg-gray-700 text-center text-white mb-1'
style='overflow: auto; '><p class='mx-4'> Nom de la série : ";
        $res.=$titre;
        $res.="</p>";
        $res.="<img src='$img' width='80%' class='mx-4'/>";
        $res.="</div>";
        $res .= "</a>";

        return $res;

    }


}