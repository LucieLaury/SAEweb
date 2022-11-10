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

    public function setMail(string $mail):void{
        $this->email = $mail;
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
    public function updateListeType(int $type, int $idSerie, bool|int $val): void{
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
            //la ligne existe : update dans feedback
            $secondQuery="";
            switch ($type) {
                case 1: // FAVORIS
                    $secondQuery = "update feedback set videoPref= :boo where email = :em and idS = :ser";
                    break;
                case 2: // EN COURS
                    $secondQuery = "update feedback set enCours= :boo where email = :em and idS = :ser";
                    break;
                case 3: // VISIONNEE
                    $secondQuery = "update feedback set videoVisionnee= :boo where email = :em and idS =:ser";
                    break;
            }
            $resultat = $bd->prepare($secondQuery);
            $resultat->bindParam(":boo", $val);
            $resultat->bindParam(":em", $this->email);
            $resultat->bindParam(":ser", $idSerie);
            $resultat->execute();
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

        //récupération de la liste listeVisionnee
        $listeVisionnee = $this->listeType(3);

        $trouveVisionnee = false;
        //pour chaque serie dans la liste EnCours
        foreach ($listeVisionnee as $serieVisionnee){
            //vérifie si la liste est trouvee
            if($serieVisionnee->id==$idSerie){
                $trouveVisionnee = true;
                break;
            }
        }

        //si la liste n'est pas totalement visionnee :
        if(!$trouveVisionnee){
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
            $query = "select visionne from episodesVisionnes where email = ? and idEpisode = ?";
            $result = $db->prepare($query);
            $result->bindParam(1, $this->email);
            $result->bindParam(2, $idEpisode);
            $result->execute();
            $row=$result->fetch();

            //si aucun résultat n'est retourné : insertion
            if(!$row){
                $query = "insert into episodesVisionnes values (?,?,?,1)";
                $result = $db->prepare($query);
                $result->bindParam(1, $this->email);
                $result->bindParam(2, $idSerie);
                $result->bindParam(3, $idEpisode);
                $result->execute();
            }
            $this->updateListeDejaVisionnee($idSerie);
        }
    }

    /**
     * Invoquée par updateListeEnCours, update de la liste Deja Visionnee
     * @param int $idSerie,
     * @return void
     */
    public function updateListeDejaVisionnee(int $idSerie){

        //récupération du nombre d'épisodes regardés
        $bd = ConnectionFactory::makeConnection();
        $query = "select count(*) from episodesVisionnes where email = ? and idSerie = ? and visionne = 1";
        $result = $bd->prepare($query);
        $result->bindParam(1, $this->email);
        $result->bindParam(2, $idSerie);
        $result->execute();
        $nbEpisodesRegardes = $result->fetch();

        //récupération du nombre total d'épisodes
        $db = ConnectionFactory::makeConnection();
        $query2 = "select count(*) from episode where serie_id = ?";
        $resultat = $db->prepare($query2);
        $resultat->bindParam(1, $idSerie);
        $resultat->execute();
        $nbEpisodesTotal = $resultat->fetch();
        //si le nombre d'épisodes regardés est égal au nombre d'épisodes total : update
        if($nbEpisodesRegardes[0]==$nbEpisodesTotal[0]){
            $this->updateListeType(self::ENCOURS,$idSerie,0);
            $this->updateListeType(self::VISIONNER,$idSerie,1);
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