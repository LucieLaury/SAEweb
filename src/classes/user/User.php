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

    public function updateListeType(int $type, int $idSerie): void{
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
                        $secondQuery = "update feedback set videoPref=true where email = ? and idS =?";
                        break;
                    case 2: // EN COURS
                        $secondQuery = "update feedback set enCours=true where email = ? and idS =?";
                        break;
                    case 3: // VISIONNEE
                        $secondQuery = "update feedback set videoVisionnee=true where email = ? and idS =?";
                        break;
                }
                $resultat = $bd->prepare($secondQuery);
                $resultat->bindParam(1, $this->email);
                $resultat->bindParam(2, $idSerie);
                $resultat->execute();
            }
            //ligne existante : on ignore
        }
        //la ligne n'existe pas : insertion d'une ligne dans feedback
        else{
            $secondQuery="";
            switch ($type) {
                case 1: // FAVORIS
                    $secondQuery = "insert into feedback (idS,email,videoPref) values (?,?,true)";
                    break;
                case 2: // EN COURS
                    $secondQuery = "insert into feedback (idS,email,enCours) values (?,?,true)";
                    break;
                case 3: // VISIONNEE
                    $secondQuery = "insert into feedback (idS,email,videoVisionnee) values (?,?,true)";
                    break;
            }
            $resultat = $bd->prepare($secondQuery);
            $resultat->bindParam(1, $idSerie);
            $resultat->bindParam(2, $this->email);
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

        //récupération de la liste en Cours
        $listeEnCours = $this->listeType(2);

        $trouveSerie = false;
        //pour chaque serie dans la liste EnCours
        foreach ($listeEnCours as $serieEnCours){
            if($serieEnCours->id==$idSerie){
                $trouveSerie = true;
                break;
            }
        }
        //si la serie n'est pas trouvee
        if(!$trouveSerie){
            $this->updateListeType(2,$idSerie);
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