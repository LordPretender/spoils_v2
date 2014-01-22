<?php

class Demandes {
    private $ftp;
    
    public function __construct() {
        //Lecture du répertoire temporaire
        $this -> ftp = new FTP("../tmp", true);
    }
    
    /**
     * Retourne toutes les demandes en attente d'approbation
     * @return String[] Tableau de chaînes qui correspond au slug (chemin relatif sans extension) du fichier.
     */
    public function getDemandes(){
        return $this -> ftp -> getFichiers(true);
    }
    
    /**
     * Il existe des demandes ?
     * @return Boolean Vrai si oui. Faux, si non.
     */
    public function existeDemandes(){
        return (count($this -> getDemandes()) > 0 ? true : false);
    }
}

?>
