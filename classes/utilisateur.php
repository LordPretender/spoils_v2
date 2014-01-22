<?php

/**
 * Classe utilisable uniquement si le pont entre le site et le forum existe.
 */
class Utilisateur {
	private $phpBB_user = null;
	
	private $login = "";
	private $email = "";
	
	private $approbateur = false;
	
    public function __construct(){
    }
	
    /**
     * Si une passerelle existe entre le site et le forum, on initialise notre objet à partir du forum.
	 * @param Classe utilisateur du forum phpBB
     */
	public function initialiser($user){
		global $approbateurs;
		
		if(isset($user)){
			$this -> phpBB_user = $user;
			
			// Start session management
			$this -> phpBB_user -> session_begin();
			
			//On ne récupère le login que si l'utilisateur est identifié.
			$this -> login = $this -> getID() == ANONYMOUS ? "" : $this -> getUserData('username_clean');
			$this -> email = $this -> getUserData('user_email');
			
			//Si l'utilisateur phpBB est un approbateur
			if(in_array($this -> login, $approbateurs)){
				define('ADMIN_START', true);
				
				$this -> approbateur = true;
			}
		}
	}
    
    /**
     * Lecture d'une info contenue dans l'attribut "data" de la classe user de phpBB.
	 * @param $param Nom de l'info à lire
     * @return Mixed Info demandée.
     */
	public function getUserData($param = ""){
		return $param == "" ? $this -> phpBB_user -> data : $this -> phpBB_user -> data[$param];
	}
	
	public function getUserColor(){
		return $this -> getUserData('user_colour');
	}
	
    /**
     * Retourne l'ID de l'utilisateur, connecté ou non
     * @return Integer ID de l'utilisateur courant.
     */
	public function getID(){
		return (int) $this -> getUserData('user_id');
	}
	
    /**
     * Retourne l'IP de l'utilisateur, connecté ou non
     * @return String IP de l'utilisateur courant.
     */
	public function getIP(){
		return $this -> getUserData('user_ïp');
	}
	
    /**
     * Retourne l'utilisateur identifié
     * @return String Login de l'utilisateur identifié.
     */
    public function getLogin(){
        return $this -> login;
    }
    
    /**
     * Retourne le login (pas le nettoyé) de l'utilisateur, connecté ou non
     * @return String login de l'utilisateur courant.
     */
	public function getUsername(){
		return $this -> getUserData('username');
	}
	
    /**
     * Retourne l'adresse mail de l'utilisateur
     * @return String Mail de l'utilisateur.
     */
	public function getMail(){
		return $this -> email;
	}
	
    /**
     * Permet de savoir si l'utilisateur en cours est un approbateur ou non.
     * @return Boolean Vrai si l'utilisateur est approbateur. Faux, sinon.
     */
    public function estApprobateur(){
        return $this -> estIdentifié() && $this -> approbateur;
    }
	
    /**
     * Permet de savoir si l'utilisateur en cours est identifié ou non.
     * @return Boolean Vrai si l'utilisateur est identifié. Faux, sinon.
     */
	public function estIdentifié(){
		return $this -> login == "" ? false : true;
	}
}

?>
