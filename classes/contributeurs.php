<?php

class Contributeurs {
	private $fichier;
	
    public function __construct() {
		//Lecture du fichier des contributeurs
        $this -> fichier = new Fichier("../contributeurs", false);
    }
	
	public function NouveauContributeur($login, $mail, $lié){
		//On s'assure que le contributeur n'existe pas déjà.
		if($this -> getMailByLogin($login) == ''){
			//Ajout
			$this -> fichier -> setParam($login, "$mail" . PARAM_SEPARATEUR_VALEUR . "$lié");
			
			//Enregistrement
			$this -> enregistrer();
		}
	}
	
    /**
     * Raccourcis vers la méthode du même nom de la classe Fichier.
     */
	public function enregistrer(){
		$this -> fichier -> enregistrer();
	}
	
    /**
     * Nombre de contributeurs
     * @return Integer Nombre total de contributeurs.
     */
	public function Total(){
		return count($this -> getListe());
	}
	
    /**
     * Récupère la liste des contributeurs
     * @return Array Liste des contributeurs
     */
	public function getListe(){
		//On récupère les paramètres du fichier
		$liste = $this -> fichier -> getParam();
		
		//On vire le titre
		unset($liste['titre']);
		
		//On renvoie le reste
		return $liste;
	}
	
    /**
     * Lecture de l'ID du login
	 * @param String $login Login à chercher
     * @return Indice trouvé correspondant à l'ID du login cherché ou faux.
     */
	public function getPosition($login){
		//On commence par récupérer les logins
		$logins = array_keys($this -> getListe());
		
		return array_search($login, $logins);
	}
	
    /**
     * A partir d'un numéro de ligne, on va retourner le login associé.
	 * @param Integer Numéro de la ligne.
     * @return String Login de l'utilisateur souhaité.
     */
	public function getLogin($indice){
		//on récupère toutes les clés qui correspondent aux logins
		$logins = array_keys($this -> getListe());
		
		return $logins[$indice];
	}
	
    /**
     * A partir d'un login, on va retourner l'adresse mail associé.
	 * @param String $login Login d'un utilisateur
     * @return String Adresse mail de l'utilisateur ou vide.
     */
	public function getMailByLogin($login){
		$retour = "";
		$item = $this -> getListe();
		
		if(isset($item[$login]))$retour = $item[$login][0];
		
		return $retour;
	}
	
    /**
     * Dans le fichier des contributeurs, la 2nde partie du contenu des paramètres est à 0 (si non lié au forum) ou à 1 (si lié au forum)
	 * On va donc, à partir du login, regarder si l'utilisateur a déjà son compte lié au forum (statut changé à la main si lié en cours de route).
	 * @param String $login Login d'un utilisateur
     * @return Boolean Vrai si le contributeur a été défini en tant que lié à son compte du forum.
     */
	public function estLié($login, $lié = -1){
		$retour = false;
		$item = $this -> getListe();
		
		//Si un statut est défini, on effectue le changement
		if($lié >= 0){
			//On change
			$item[$login][1] = $lié;
			
			//Puis on reporte au Fichier
			$this -> fichier -> setParam($login, $item[$login]);
		}
		
		if(isset($item[$login]))$retour = $item[$login][1] == 1 ? true : false;
		
		return $retour;
	}
}

?>
