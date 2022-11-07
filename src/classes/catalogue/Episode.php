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


    //CONST
    public function __construct(int $id, int $num, string $ti, string $resu, int $dur, string $fi){
        $this->id = $id;
        $this->numero = $num;
        $this->titre = $ti;
        $this->resume = $resu;
        $this->duree = $dur;
        $this->file = $fi;
    }

    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }


    public static function find(string $titre): Episode{
        $bd = ConnectionFactory::makeConnection();
        $c1 = $bd->prepare("Select * from episode where titre= :ti ;");
        $c1->bindParam(":ti",$titre);
        $c1->execute();
        $creer=false;
        while ($row = $c1->fetch())
        {
            if (!$creer) {
                $num = $row['numero'];
                $ti = $row['titre'];
                $resum = $row['resume'];
                $dur = $row['duree'];
                $fil = $row['file'];
                $episode = new Episode($num, $ti, $resum, $dur, $fil);
                $creer = true;
            }
        }
        return $episode;
    }

}