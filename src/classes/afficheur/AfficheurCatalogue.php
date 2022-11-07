<?php

namespace iutnc\netVOD\catalogue;

use iutnc\netVOD\Afficheur;
use iutnc\netVOD\db\ConnectionFactory;

class AfficheurCatalogue extends Afficheur
{

    private Catalogue $catalogue;

    public function __construct()
    {
        parent::__construct();
        $this->catalogue = Catalogue::addSerieDB();
    }




    public function execute(): string
    {

        if ($this->http_method == "GET"){
            $res = $this->affichageGlo();
        }

    }



    public function affichageGlo():string{

    }

}