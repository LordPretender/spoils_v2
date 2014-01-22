<?php

/**
 * Classe qui permet de lire le contenu d'un répertoire du FTP.
 */
class FTP {
    private $contenu;
    private $folder;

    /**
     * Connexion à un répertoire du ftp avec lecture du contenu
     * @param String $folder chemin absolu du répertoire à lire.
     */
    public function __construct($folder = "", $tri_date = false){
        $this -> folder = $folder;

        //Lecture du contenu
        $this -> contenu = @scandir($this ->getPath());
		
		//Tri par date
		if($tri_date)$this -> trierParDate();
    }

	private function trierParDate(){
		$tmp = array();
		
		for($x = 0; $x < count($this -> contenu); $x++){
			$tmp[$this -> contenu[$x]] = filemtime($this ->getPath() . "/" . $this -> contenu[$x]);
		}
		
		asort($tmp);
		$this -> contenu = array_keys($tmp);
	}

    /**
     * A partir du répertoire courant et du nom de fichier fourni, on génère un slug pour ce dernier.
     * @param String $nom Nom du fichier
     * @param Boolean $relatif Vrai : les slugs seront générés sans l'ajout du répertoire en cours. Faux : les slugs seront générés avec le slug du répertoire en cours.
     * @return String Slug généré.
     */
    private function getSlug($nom, $relatif = false){
        return ($this -> folder != '' && !$relatif ? $this -> folder . "/" : '') . str_replace(PAGE_EXTENSION, "", $nom);
    }

    /**
     * Construit le chemin absolu vers le répertoire spécifié.
     * @return String Chemin absolu du répertoire courant.
     */
    private function getPath(){
        return FOLDER_PAGES . ($this -> folder == "" ? "" : "/") . $this -> folder;
    }

    /**
     * Lire tous les fichiers du répertoire en cours, avec les sous-repertoires.
     * @return Array Tableau, trié sur la date de modification la plus récente en 1er, avec en clef le slug et en valeur la date de modification.
     */
    public function getTousLesFichiers(){
        $fichiers = array();
        $berk = array('.', '..'); // ne pas tenir compte de ses répertoires / fichiers
        
        //On remplit le tableau
        foreach ($this -> contenu as $element) {
            if(!in_array($element, $berk)){
                //Chemin vers le fichier en cours
                $path_absolu = $this -> getPath() . "/$element";
                $path_relatif = ($this -> folder == "" ? "" : $this -> folder . "/") . "$element";
                $slug = preg_replace("#(/?" . FOLDER_INDEX . ")?" . PAGE_EXTENSION . "#i", "", $path_relatif);

                //Ajout dans le tableau
                if(is_dir($path_absolu)){
                    $ftp = new FTP($path_relatif);
                    $fichiers = array_merge($fichiers, $ftp -> getTousLesFichiers());
                }else $fichiers[$slug] = date ("Y-m-d H:i:s", filemtime($path_absolu));
            }
        }

        //On trie
        arsort($fichiers);

        return $fichiers;
    }

    /**
     * Méthode qui permet de récupérer tous les fichiers du répertoire en cours.
     * @param Boolean $relatif Vrai : les slugs seront générés sans l'ajout du répertoire en cours. Faux : les slugs seront générés avec le slug du répertoire en cours.
     * @return String[] Tableau de chaînes qui correspond au slug (chemin relatif sans extension) du fichier.
     */
    public function getFichiers($relatif = false){
        $slugs = array();
        
        if(is_array($this -> contenu)){
            foreach ($this -> contenu as $elt){
                //On garde tous les fichiers (sauf celui qui correspond au répertoire)
                if(!is_dir($this ->getPath() . "/" . $elt) && $elt != "." && $elt != "..")$slugs[] = $this ->getSlug($elt, $relatif);
            }
        }

        return $slugs;
    }

    /**
     * Méthode qui permet de récupérer tous les répertoires contenus dans le répertoire en cours
     * @return String[] Tableau de slugs (chemin relatif).
     */
    public function getDossiers(){
        $slugs = array();

        if(is_array($this -> contenu)){
            foreach ($this -> contenu as $elt){			
                    if(is_dir($this ->getPath() . "/" . $elt) && $elt != "." && $elt != "..")$slugs[] = $this ->getSlug($elt);
            }
        }

        return $slugs;
    }
}

?>