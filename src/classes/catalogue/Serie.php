<?php

namespace iutnc\netVOD\catalogue;

use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ExceptionListe;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class Serie
{

    //ATTRIBUTS
    private int $id;
    private string $titre;
    private string $descriptif;
    private int $annee;
    private string $date;
    private array $episodes;
    private int $nbEpisodes;

    //CONST
    public function __construct(int $id, string $ti, string $desc, int $ann, string $da){
        $this->id = $id;
        $this->titre = $ti;
        $this->descriptif = $desc;
        $this->annee = $ann;
        $this->date = $da;
        $this->nbEpisodes = 0;
        $this->episodes = array();
    }


    //METHODES
    public static function find(string $titre): Serie{
        $bd = ConnectionFactory::makeConnection();
        $c1 = $bd->prepare("Select * from serie where titre= :ti ;");
        $c1->bindParam(":ti",$titre);
        $c1->execute();
        $creer=false;
        while ($row = $c1->fetch())
        {
            if (!$creer) {
                $id = $row['id'];
                $ti = $row['titre'];
                $desc = $row['descriptif'];
                $ann = $row['annee'];
                $date = $row['date_ajout'];
                $serie = new Serie($id, $ti, $desc, $ann, $date);
                $serie->addEpisodeBD();
                $creer = true;
            }
        }
        return $serie;
    }



    public function addEpisode(Episode $episode):void{
        $verif = false;
        foreach ($this->episodes as $epi){
            if ($epi == $episode) $verif = true;
        }
        if (!$verif){
            $this->episodes[$this->nbEpisodes] = $episode;
            $this->nbEpisodes++;

        }
    }

    public function supEpisode(Episode|int $episode):void{

        if (gettype($episode)=="integer"){
            unset($this->episodes[$episode-1]);
        } else if (gettype($episode)=="string"){

            for($i = 0; $i<$this->nbEpisodes; $i++){
                $epi = $this->episodes[$i];
                if ($epi == $episode) {
                    unset($this->episodes[$i]);
                }
            }
        }else {
            throw new ExceptionListe("Episode introuvable dans la série");
        }
        $this->nbEpisodes--;


    }


    public function addEpisodeBD(){
        $db = ConnectionFactory::makeConnection();
        $req = $db->prepare("SELECT titre from episode where serie_id = :idS ;");
        $req->bindParam(":idS", $this->id);
        $req->execute();
        while ($row = $req->fetch()){
            $episode = Episode::find($row['titre']);
            $this->addEpisode($episode);
        }
    }

    /**
     * @throws ProprieteInexistanteException
     */
    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }




}