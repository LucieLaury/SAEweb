<?php
namespace iutnc\netVOD\user;
use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\catalogue\Episode as Episode;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ProprieteInexistanteException;

class User
{
    const FAVORIS = 1;
    const ENCOURS = 2;
    const VISIONNER = 3;


    private string $email;
    private string $nom;
    private string $prenom;
    private string $noCarte;
    /**
     * @param string $email
     * @param string $nom
     * @param string $prenom
     * @param string $noCarte
     */
    public function __construct(string $email, string $nom, string $prenom, string $noCarte)
    {
        $this->email = $email;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->noCarte = $noCarte;
    }

    public function listeType(int $type): array {
        $tab = [];
        $query = "";
        switch ($type) {
            case 1: // FAVORIS
                $query = "select idS from feedback where videoPref = true and email = ?;";
                break;
            case 2: // EN COURS
                $query = "select idS from feedback where enCours = true and email = ?;";
                break;
            case 3: // VISIONNEE
                $query = "select idS from feedback where videoVisionnee = true and email = ?;";
                break;
        }
        $bd = ConnectionFactory::makeConnection();
        $result = $bd->prepare($query);
        $result->bindParam(1, $this->email);
        $result->execute();
        while($data = $result->fetch()) {
            $tab[] = Serie::find((int) $data["idS"]);
        }
        return $tab;
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
        $idEpisode = $e->id;
        $req = $db->prepare("SELECT serie_id from episode 
             where episode.id= :id ;");
        $req->bindParam(":id", $idEpisode);
        $req->execute();
        $row = $req->fetch();
        $idSerie = $row['serie_id'];

        $trouveSerie = false;
        //pour chaque serie dans la liste EnCours
        foreach ($this->enCours as $serieEnCours){
            if($serieEnCours->id==$idSerie){
                $trouveSerie = true;
                break;
            }
        }
        if(!$trouveSerie){
            $enCours[]= Serie::find($idSerie);
        }
    }

    public function LikeOuPas(int $idserie){
        $db = ConnectionFactory::makeConnection();
        $serie = Serie::find($idserie);
        if(in_array($serie, $this->favoris)){
            foreach ($this->favoris as $cle=>$value ){
                if($value===$serie){
                    unset($this->favoris[$cle]);
                }
            }
            $req = $db->prepare("SELECT idS from feedback
             where idS= :id and email= :mail;");
            $req->bindParam(":id", $idserie);
            $req->bindParam(":mail", $this->email);
            $req->execute();
            if( ($req->fetch())!=null){
                $db->execute("insert into feedback(idS, email, videoPref) values ( $this->email, $idserie, true);");
            }else{
                $db->execute("UPDATE feedback SET videoPref = true WHERE idS = $idserie and email=$this->email ;");
            }
        }else{
            $this->favoris[] = $serie;
            $db->prepare("UPDATE feedback SET videoPref = false WHERE idS = $idserie and email = $this->email;");

        }

    }




}