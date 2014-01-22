<?php

class Contribution extends SiteTemplate {
    protected $msg_type = "";
    protected $msg_text = "";
    
    protected $login = "";
    protected $email = "";
    protected $titre = "";
    protected $spoil = "";
	protected $type = -1;
    
    protected $mode = -1;
    
    /**
     * En mode création, correspond au slug du parent.
	 * En mode modification, correspond au slug de la page à modifier
     * @var String
     */
    protected $slug = "";
    
    /**
     * Slug du fichier qui sera créé ou modifié.
     * @var String
     */
    protected $slug_contribution;
    
    /**
     * Ouverture d'une demande ?
     * @var Boolean Vrai si on vient d'ouvrir une demande à approuver. Faux, sinon.
     */
	protected $modeApprobation = false;
	
    /**
     * Classe mère permettant de créer ou modifier des fichiers (simple ou présentation de catégorie).
     * @param Integer $mode Mode d'accès au fichier. 0 -> Modification, 1 -> Création d'un fichier, 2 -> Création d'une catégorie.
     * @param String $slugFourni Paramètre optionel sur l'ID d'une page.
     */
    public function __construct($mode, $slugFourni = ""){
        //Le slug fourni en paramètre est prioritaire sur le slug dispo en session (si plus de slug en session, on tente de lire le champ)
		if($slugFourni == ""){
			//Lorsqu'on valide le formulaire, il faut récupérer le slug via le formulaire.
			//Nous n'avons aucun contrôle sur le temps que mettra l'utilisateur à remplir le formulaire.
			//La variable de session pourrait donc ne plus exister.
			$this -> slug = $_POST ? $_POST['origine'] : SiteSession::getVariable("slug");
		}else $this -> slug = $slugFourni;
        
        //On lit si la page en cours est en attente ou non.
		if($_POST){
			$tmp = new FichierTMP($this -> slug);
			$enAttente = $tmp -> existe();
		}else $enAttente = (SiteSession::isExist("enAttente")) ? true : false;
        
        //On lit le fichier du parent
        parent::__construct($this -> slug, $enAttente);
        $this -> mode = $mode;
        
        //Définition du template pour le contenu
        $this -> gabarit = "contribution";
        
        //On s'assure que l'on a bien le droit d'accéder au formulaire
        $this ->accèsAutorisé();
        
		//Initialisation des variables qui seront enregistrés dans le fichier
		$this -> initialiser();
		
        //Modification du titre et de la déscription (META)
        $this -> préfixe_description = ($mode > 0 ? "Nouvelle" : "Modifier") . " " . ($mode > 1 ? "catégorie" : "page");
        $this -> titre_suffixe = $this -> préfixe_description;
        $this -> tpl -> assign( "formulaire_fieldset", $this -> préfixe_description );
    }
	
    /**
     * Méthode permettant d'initialiser les attributs qui seront enregistrés dans le fichier.
     */
    protected function initialiser(){
		//Si utilisateur identifié, on mémorie son login et son mail
		if($this -> user -> estIdentifié() && !$this -> modeApprobation){
	        $this -> login = $this -> user -> getLogin();
	        $this -> email = $this -> user -> getMail();
		}else{ //Sinon, on récupère les infos qu'il a saisi
	        $this -> login = isset($_POST['login']) ? strtolower(supprimerAccents($_POST['login'])) : '';
	        $this -> email = isset($_POST['email']) ? $_POST['email'] : '';
		}
		
        //Contenu du formulaire
        $this -> titre = isset($_POST['titre']) ? stripslashes($_POST['titre']) : '';
        $this -> spoil = isset($_POST['spoil']) ? stripslashes($_POST['spoil']) : '';
        $this -> type = isset($_POST['type_contenu']) ? intval($_POST['type_contenu']) : -1;
	}
	
    /**
     * Permet de s'assurer que l'on a le droit d'accéder au formulaire en regardant les permissions du parent.
     */
    protected function accèsAutorisé(){
		global $types_catégories;
        $interdit = true;
        
        //Nous devons avoir un parent (la page d'accueil est interdite en écriture).
        if($this -> page -> getSlug() != ""){
            switch ($this -> mode) {
                case 0: //Modification
                    //On doit juste s'assurer que le fichier à modifier existe et qu'il n'est pas verrouillé (sauf si approbateur)
                    if($this -> page -> existe() && ((!$this -> page -> estBloqué()) || ($this -> page -> estBloqué() && $this -> user -> estApprobateur())))$interdit = false;
                    break;
                
                case 1: //Création d'un fichier
                    //On ne peut créer un fichier que si le parent est un répertoire et qu'il accepte la création de fichiers
                    if($this -> page -> estCatégorie() && !in_array($this -> page -> getTypePages(), $types_catégories))$interdit = false;
                    break;
                
                case 2: //Création d'une catégorie
                    //On ne peut créer une catégorie que si le parent est une catégorie et qu'il accepte la création de catégories
                    if($this -> page -> estCatégorie() && in_array($this -> page -> getTypePages(), $types_catégories))$interdit = false;
                    break;
            }
        }
        
        //Redirection vers la page d'accueil
        if($interdit){
            $this -> email = "";
            
            header('Location: /');
			die;
        }
    }
    
    /**
     * Permet d'afficher un message d'erreur afin de prévenir le champ invalide.
     * @param String $texte Texte à afficher
     */
    protected function messageErreur($texte){
        //Affichage du message d'erreur
        $this -> msg_type = "error";
        $this -> msg_text = $texte;
    }
    
    /**
     * Permet de contrôler le contenu du formulaire.
     * @return Boolean Vrai si le formulaire est conforme. Faux, sinon.
     */
    private function contrôler(){
        $ok = true;
        
        //Champ anti-spam
        $spam_check = isset($_POST['spam_check']) ? $_POST['spam_check'] : '';
        
        if($this -> login == '' || $this -> email == '' || $this -> titre == '' || $this -> spoil == ''){
            $this ->messageErreur("Les champs marqués d'une étoile (*) sont requis.");
            
            $ok = false;
        }elseif (filter_var($this -> email, FILTER_VALIDATE_EMAIL) !== $this -> email){
            $this ->messageErreur("L'adresse mail saisi est incorrect.");
            
            $ok = false;
        }elseif($spam_check != ""){
            $this ->messageErreur("Le dernier champ doit être laissé vide.");
            
            $ok = false;
        }
        
        //Si nous avons trouvé un collaborateur et que l'email ne correspond pas à celui saisi...
		$email_lu = $this -> contributeurs -> getMailByLogin($this -> login);
        if($email_lu != "" && $email_lu != $this -> email){
            $this ->messageErreur("Le login saisi existe. Merci de saisir l'adresse email qui lui est associé ou de choisir un autre login.");
            
            $ok = false;
        }
        
        return $ok;
    }
    
    /**
     * Création d'un objet Fichier ou FichierTMP en fonction du type de contribution et si l'utilisateur est identifié ou non.
     * @return Page Page lue.
     */
    protected function lectureFichier(){
        //Création d'un fichier temporaire si utilisateur non identifié.
        $fichier = new FichierTMP();
        $this -> slug_contribution = $fichier ->getNom();
        
        return $fichier;
    }
    
    protected function CréerValider($fichier){
        //Initialisation du fichier temporaire
        $fichier -> setParam("slug", $this -> page -> getSlug());
        if($this -> mode > 1)$fichier -> setParam("type", $this -> type);
        $fichier -> setParam("login", $this -> login);
        $fichier -> setParam("mail", $this -> email);
        $fichier -> setParam("mode", $this -> mode);
        $fichier -> setParam("date", date ("d/m/Y H:i"));
        $fichier -> setParam("ip", $_SERVER["REMOTE_ADDR"]);

        //Enregistrement
        $fichier ->enregistrer();

        //Affichage du succès.
        $this -> msg_text = "La demande de " . ($this -> mode > 0 ? "création" : "modification") . " a bien été envoyée. Vous recevrez un mail lorsque votre demande aura été traitée et validée.";
    }
	
    protected function avantChargement(){
        global $types;
		
        //Formulaire validé ?
        if($_POST){
            //Dans tous les cas, on affichera un message, soit d'erreur, soit de succès.
            $this -> msg_type = "success";
            
            //On teste la conformité
            if($this ->contrôler()){
                //Création du fichier à écrire
                $fichier = $this -> lectureFichier();
                
                //Ajout ou modification du titre et contenu
                $fichier -> setParam("titre", $this -> titre);
                $fichier -> setContenu($this -> spoil);

                //Création ou validation d'une demande
                $this -> CréerValider($fichier);
            }
        }elseif($this -> mode == 0 && !$this -> modeApprobation) { //Chargement du formulaire à partir de la page à modifier.
            $this -> titre = $this -> page -> getTitre();
            $this -> spoil = $this -> page -> getContenu(false);
        }

        //Contenu du formulaire
        $this -> tpl -> assign( "formulaire_slug", $this -> slug );
        $this -> tpl -> assign( "formulaire_login", $this -> login );
        $this -> tpl -> assign( "formulaire_email", $this -> email );
        $this -> tpl -> assign( "formulaire_titre", $this -> titre );
        $this -> tpl -> assign( "formulaire_spoil", $this -> spoil );
        $this -> tpl -> assign( "formulaire", "/" . (!$this -> modeApprobation ? $this -> slugCollaboratif($this -> mode) : 'approuver') . "/" );
        $this -> tpl -> assign( "mode", $this -> mode );
        $this -> tpl -> assign( "mode_approbation", $this -> modeApprobation );
        $this -> tpl -> assign( "type_contenu", $types );
        $this -> tpl -> assign( "type_choisi", $this -> type );
        
        if($this -> msg_type != ""){
            $this -> tpl -> assign( "msg_type", $this -> msg_type );
            $this -> tpl -> assign( "msg_text", $this -> msg_text );                
        }
    }
}

?>
