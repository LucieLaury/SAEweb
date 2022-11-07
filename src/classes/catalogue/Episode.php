<?php

namespace iutnc\netVOD;

use PDO;

class Episode
{

    //ATTRIBUTS
    private int $numero;
    private string $titre;
    private string $resume;
    private int $duree;
    private string $file;


    //CONST
    public function __construct(string $titre){

    }


    public static function find(string $titre, PDO $bd): Episode{
        $c1 = $bd->prepare("Select * from episode where titre= :ti ;");
        $c1->bindParam(":ti",$titre);
        $c1->execute();
        $p=null;
        $creer=false;
        while ($d = $c1->fetch())
        {
            if (!$creer) {
                $p = new Playlist($d['nom']);
                $creer = true;
            }
            $al = new AlbumTrack($d['titre'],$d['nom']);
            $al->duree=$d['duree'];
            $p->addPiste($al);
        }
        return $p;
    }

}