<?php
namespace iutnc\netVOD\user;
use iutnc\netVOD\catalogue\Serie;
use iutnc\netVOD\catalogue\Episode as Episode;
use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\exception\ProprieteInexistanteException;
use PDO;

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

    /**
     * Fonction qui retourne la liste en fonction de son titre
     * @param int $type type de la liste
     * @return array liste
     */
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
     * Fonction qui effectue une update d'une liste par rapport à une série
     * @param int $type type de la série
     * @param int $idSerie id de la série où effectuer une update
     * @param bool $val valeur de la liste à changer
     * @return void
     */
    public function updateListeType(int $type, int $idSerie, bool $val): void{
        $query = "";
        switch ($type) {
            case 1: // FAVORIS
                $query = "select idS, videoPref from feedback where email = ? and idS =?;";
                break;
            case 2: // EN COURS
                $query = "select idS, enCours from feedback where email = ? and idS =?;";
                break;
            case 3: // VISIONNEE
                $query = "select idS, videoVisionnee from feedback where email = ? and idS =?;";
                break;
        }
        $bd = ConnectionFactory::makeConnection();
        $result = $bd->prepare($query);
        $result->bindParam(1, $this->email);
        $result->bindParam(2, $idSerie);
        $result->execute();
        //vérifie si la ligne existe + récupère les données
        $lineExist = false;
        if($data = $result->fetch(PDO::FETCH_NUM)) $lineExist = true;

        //si la ligne existe
        if($lineExist){
            //la ligne existe, la valeur n'est pas en true : update dans feedback
            if($data[1]==1){
                $secondQuery="";
                switch ($type) {
                    case 1: // FAVORIS
                        $secondQuery = "update feedback set videoPref=? where email = ? and idS =?";
                        break;
                    case 2: // EN COURS
                        $secondQuery = "update feedback set enCours=? where email = ? and idS =?";
                        break;
                    case 3: // VISIONNEE
                        $secondQuery = "update feedback set videoVisionnee=? where email = ? and idS =?";
                        break;
                }
                $resultat = $bd->prepare($secondQuery);
                $resultat->bindParam(1, $val);
                $resultat->bindParam(2, $this->email);
                $resultat->bindParam(3, $idSerie);
                $resultat->execute();
            }
            //ligne existante : on ignore
        }
        //la ligne n'existe pas : insertion d'une ligne dans feedback
        else{
            $secondQuery="";
            switch ($type) {
                case 1: // FAVORIS
                    $secondQuery = "insert into feedback (idS,email,videoPref) values (?,?,?)";
                    break;
                case 2: // EN COURS
                    $secondQuery = "insert into feedback (idS,email,enCours) values (?,?,?)";
                    break;
                case 3: // VISIONNEE
                    $secondQuery = "insert into feedback (idS,email,videoVisionnee) values (?,?,?)";
                    break;
            }
            $resultat = $bd->prepare($secondQuery);
            $resultat->bindParam(1, $idSerie);
            $resultat->bindParam(2, $this->email);
            $resultat->bindParam(3, $val);
            $resultat->execute();
        }
    }

    /**
     * @throws ProprieteInexistanteException
     */
    public function __get(string $attribut):mixed {
        if (property_exists ($this, $attribut)) return $this->$attribut;
        throw new ProprieteInexistanteException ("$attribut: propriété inexistante");
    }

    /**
     * Invoquée par afficheurEpisode, modifie la liste En Cours et la table episodesVisionnes
     * Invoque par la suite updateListeDejaVisionne
     * @param Episode $e épisode ajouté à la série
     * @return void
     */
    public function updateListeEnCours(Episode $e): void
    {
        //---1ère partie : modification de la liste en Cours

        //récupération de l'ID de la série
        $db = ConnectionFactory::makeConnection();
        $idEpisode = $e->id;
        $req = $db->prepare("SELECT serie_id from episode 
             where episode.id= :id ;");
        $req->bindParam(":id", $idEpisode);
        $req->execute();
        $row = $req->fetch();
        $idSerie = $row['serie_id'];

        //récupération de la liste en Cours
        $listeEnCours = $this->listeType(2);

        $trouveSerie = false;
        //pour chaque serie dans la liste EnCours
        foreach ($listeEnCours as $serieEnCours){
            //vérifie si la liste est trouvee
            if($serieEnCours->id==$idSerie){
                $trouveSerie = true;
                break;
            }
        }

        //si la serie n'est pas trouvee
        if(!$trouveSerie){
            $this->updateListeType(2,$idSerie,true);
        }

        //---2ère partie : enregistrement de l'épisode dans la table episodeVisionnes et appel de la fonction updateListeDejaVisonnee
        $query = "select count(*) from episodesVisionnes where email = ? and idEpisode = ?";
        $result = $db->prepare($query);
        $result->bindParam(1, $this->email);
        $result->bindParam(2, $idEpisode);
        $result->execute();
        $row=null;
        //si aucun résultat n'est retourné : insertion
        if(!($row=$result->fetch())){
            $query = "insert into episodesVisionnes values (?,?,?,true)";
            $result = $db->prepare($query);
            $result->bindParam(1, $this->email);
            $result->bindParam(2, $idSerie);
            $result->bindParam(3, $idEpisode);
            $result->execute();
        }
        $this->updateListeDejaVisionnee($e);
    }

    /**
     * Invoquée par updateListeEnCours, update de la liste Deja Visionnee
     * @param Episode|Serie $serie, conversion en série dans tous les cas
     * @return void
     */
    public function updateListeDejaVisionnee(Episode|Serie $serie){
        //si le paramètre est un épisode : cherche la série liée à l'épisode
        if(is_a($serie,"Episode")){
            $serie = Serie::find($serie->idSerie);
        }
        $id = $serie->id;
        $bd = ConnectionFactory::makeConnection();

        //récupération du nombre d'épisodes regardés
        $query = "select count(*) from episodesVisionnes where email = ? and idSerie = ?";
        $result = $bd->prepare($query);
        $result->bindParam(1, $this->email);
        $result->bindParam(2, $id);
        $result->execute();
        $nbEpisodesRegardes = (int) $result->fetch();

        //récupération du nombre total d'épisodes
        $query2 = "select count(*) from episode where idSerie = ?";
        $resultat = $bd->prepare($query2);
        $resultat->bindParam(1, $id);
        $resultat->execute();
        $nbEpisodesTotal = (int) $resultat->fetch();

        if($nbEpisodesRegardes==$nbEpisodesTotal){
            $this->updateListeType(2,$id,false);
            $this->updateListeType(3,$id,true);
        }
    }

    public function LikeOuPas(int $idserie){
        $db = ConnectionFactory::makeConnection();
        $serie = Serie::find($idserie);
        $favoris = $this->listeType(User::FAVORIS);
        if(!in_array($serie, $favoris)){
            $req = $db->prepare("SELECT idS from feedback
             where idS= :id and email= :mail;");
            $req->bindParam(":id", $idserie);
            $req->bindParam(":mail", $this->email);
            $req->execute();
            if( ($req->fetch())!=null){
                $req2 = $db->prepare("UPDATE feedback SET videoPref = :bool WHERE idS = :id and email = :mail ;");

                $b = true;
                $req2->bindParam(":bool", $b);
                $req2->bindParam(":id", $idserie);
                $req2->bindParam(":mail", $this->email);
                $req2->execute();
            }else{
                $req2 = $db->prepare("insert into feedback(idS, email, videoPref) values ( :id, :mail,  :bool);");
                $bo=true;
                $req2->bindParam(":bool", $bo);
                $req2->bindParam(":id", $idserie);
                $req2->bindParam(":mail", $this->email);
                $req2->execute();
            }
        }else{
            $req3 = $db->prepare("UPDATE feedback SET videoPref = :bool WHERE idS = :id and email = :mail ;");
            $bo = false;
            $req3->bindParam(":bool", $bo);
                $req3->bindParam(":id", $idserie);
                $req3->bindParam(":mail", $this->email);
                $req3->execute();
        }
    }


}