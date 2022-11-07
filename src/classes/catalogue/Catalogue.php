<?php

namespace iutnc\netVOD\catalogue;

use iutnc\netVOD\exception\ExceptionListe;

class Catalogue
{

    private array $series;
    private int $nbSeries;

    public function __construct(){
        $this->series = array();
    }

    public function addSerie(Serie $serie): void{
        $verif = false;
        foreach ($this->series as $ser){
            if ($ser == $serie) $verif = true;
        }
        if (!$verif){
            $this->series[$this->nbSeries] = $serie;
            $this->nbSeries++;
        }
    }

    public function supSerie(Serie|int $serie):void{
        if (gettype($serie)=="integer"){
            unset($this->series[$serie-1]);
        } else if (gettype($serie)=="string"){

            for($i = 0; $i<$this->nbSeries; $i++){
                $ser = $this->series[$i];
                if ($ser == $serie) {
                    unset($this->series[$i]);
                }
            }
        } else {
            throw new ExceptionListe("Serie introuvable dans le catalogue");
        }
        $this->nbSeries--;
    }


}