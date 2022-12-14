<?php

namespace iutnc\netVOD\catalogue;



use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class Episode{

    //ATTRIBUTS
    private int $id;
    private int $numero;
    private string $titre;
    private string $resume;
    private int $duree;
    private string $file;
    private int $idSerie;


    //CONST
    public function __construct(int $id, int $num, string $ti, string $resu, int $dur, string $fi, int $idSerie){
        $this->id = $id;
        $this->numero = $num;
        $this->titre = $ti;
        $this->resume = $resu;
        $this->duree = $dur;
        $this->file = $fi;
        $this->idSerie = $idSerie;
    }

    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }


    public static function find(string|int $titre): Episode{
        $bd = ConnectionFactory::makeConnection();
        if (is_int($titre)){
            $c1 = $bd->prepare("Select * from episode where id= :ti ;");
        }
        else{
            $c1 = $bd->prepare("Select * from episode where titre= :ti ;");
        }
        $c1->bindParam(":ti",$titre);
        $c1->execute();
        $creer=false;
        while ($row = $c1->fetch())
        {
            if (!$creer) {
                $id = $row['id'];
                $num = $row['numero'];
                $ti = $row['titre'];
                $resum = $row['resume'];
                $dur = $row['duree'];
                $fil = $row['file'];
                $idS = $row['serie_id'];
                $episode = new Episode($id, $num, $ti, $resum, $dur, $fil,$idS);
                $creer = true;
            }
        }
        return $episode;
    }

}