<?php

class FichierTMP extends Fichier {
    private $nom;
    
    public function __construct($nom = ""){
        //Génération du nom de fichier
        if($nom == "")$nom = $_SERVER["REMOTE_ADDR"] . "_" . time();
        $this -> nom = $nom;
        
        parent::__construct("../tmp/$nom", true);
    }
    
    public function getNom(){
        return $this -> nom;
    }
}

?>