<?php
namespace iutnc\netVOD\user;
use iutnc\netVOD\catalogue\Serie;
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




}