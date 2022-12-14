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
    private string $img;
    private float $note;

    //CONST
    public function __construct(int $id, string $ti, string $desc, int $ann, string $da, string $img)
    {
        $this->id = $id;
        $this->titre = $ti;
        $this->descriptif = $desc;
        $this->annee = $ann;
        $this->date = $da;
        $this->nbEpisodes = 0;
        $this->img = $img;
        $this->episodes = array();
        $this->note = $this->calcMoySerie($id);
    }


    //METHODES
    public static function find(string|int $titre): Serie
    {
        $bd = ConnectionFactory::makeConnection();
        if (is_int($titre))
            $c1 = $bd->prepare("Select * from serie where id= :ti ;");
        else
            $c1 = $bd->prepare("Select * from serie where titre= :ti ;");
        $c1->bindParam(":ti", $titre);
        $c1->execute();
        $creer = false;
        while ($row = $c1->fetch()) {
            if (!$creer) {
                $id = $row['id'];
                $ti = $row['titre'];
                $desc = $row['descriptif'];
                $ann = $row['annee'];
                $date = $row['date_ajout'];
                $img = $row['img'];
                $serie = new Serie($id, $ti, $desc, $ann, $date, $img);
                $serie->addEpisodeBD();
                $creer = true;
            }
        }
        return $serie;
    }


    public function addEpisode(Episode $episode): void
    {
        $verif = false;
        foreach ($this->episodes as $epi) {
            if ($epi == $episode) $verif = true;
        }
        if (!$verif) {
            $this->episodes[$this->nbEpisodes] = $episode;
            $this->nbEpisodes++;

        }
    }

    public function supEpisode(Episode|int $episode): void
    {

        if (gettype($episode) == "integer") {
            unset($this->episodes[$episode - 1]);
        } else if (gettype($episode) == "string") {

            for ($i = 0; $i < $this->nbEpisodes; $i++) {
                $epi = $this->episodes[$i];
                if ($epi == $episode) {
                    unset($this->episodes[$i]);
                }
            }
        } else {
            throw new ExceptionListe("Episode introuvable dans la s??rie");
        }
        $this->nbEpisodes--;


    }


    public function addEpisodeBD()
    {
        $db = ConnectionFactory::makeConnection();
        $req = $db->prepare("SELECT titre from episode where serie_id = :idS ;");
        $req->bindParam(":idS", $this->id);
        $req->execute();
        while ($row = $req->fetch()) {
            $episode = Episode::find($row['titre']);
            $this->addEpisode($episode);
        }
    }

    /**
     * @throws ProprieteInexistanteException
     */
    public function __get(string $attribut): mixed
    {
        if (property_exists($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propri??t?? inexistante");
    }

    private function calcMoySerie($id): float
    {
        $bd = ConnectionFactory::makeConnection();
        $query = $bd->prepare("select note, email from feedback where idS = ?");
        $query->bindParam(1, $id);
        $query->execute();
        $tot = 0;
        $div = 0;
        while ($data = $query->fetch(\PDO::FETCH_ASSOC)) {
            if ($data['note'] != null) {
                $tot += $data['note'];
                $div++;
            }
        }
        if ($div != 0) $res = $tot / $div;
        else $res = 0;
        return $res;
    }


}