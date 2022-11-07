<?php
namespace iutnc\netVOD\user;
use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\catalogue\Episode as Episode;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class User
{
    private string $email;
    private string $privilege;
    private array $favoris;
    private array $enCours;

    /**
     * @param string $email
     * @param string $privilege
     */
    public function __construct(string $email, string $privilege)
    {
        $this->email = $email;
        $this->privilege = $privilege;
        $this->favoris = [];
        $this->enCours = [];
    }

    /**
     * @throws ProprieteInexistanteException
     */
    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }

    /**
     * @param Episode $e épisode ajouté à la série
     * @return void
     */
    public function updateListeEnCours(Episode $e): void
    {
        $db = ConnectionFactory::makeConnection();
        $titreEpisode = $e->titre;
        $req = $db->prepare("SELECT serie_id,titre from serie 
             INNER JOIN episode ON episode.serie_id = serie.id
             where episode.titre=?;");
        $req->bindParam("?", $titreEpisode);
        $req->execute();
        $row = $req->fetch();
        $idserie = $row['serie_id'];
        $titreSerie = $row['titre'];
        $trouveSerie = false;
        //pour chaque serie dans la liste EnCours
        foreach ($this->enCours as $serieEnCours){
            if($serieEnCours->id==$idserie){
                $trouveSerie = true;
                break;
            }
        }
        if(!$trouveSerie){
            $enCours[]= Serie::find($titreSerie);
        }
    }




}