<?php
namespace iutnc\netVOD\user;
use iutnc\netVOD\catalogue\Serie;

class User
{
    private string $email;
    private string $privilege;
    private Serie $favoris;
    private Serie $enCours;

    /**
     * @param string $email
     * @param string $privilege
     */
    public function __construct(string $email, string $privilege)
    {
        $this->email = $email;
        $this->privilege = $privilege;
        $this->favoris = [];
        $this->dejaVu = [];
        $this->enCours = [];
    }




}