<?php

namespace iutnc\netVOD\catalogue;

use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ExceptionListe;
use iutnc\netVOD\exception\ProprieteInexistanteException;

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


    public static function addSerieDB(): Catalogue{
        $catalogue = new Catalogue();
        //récupération de chaque série
        $db = ConnectionFactory::makeConnection();
        $req = $db->query("SELECT titre from serie;");
        $req->execute();
        while ($row = $req->fetch()){
            $serie = Serie::find($row['titre']);
            $catalogue->addSerie($serie);
        }
        return $catalogue;
    }

    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }


}