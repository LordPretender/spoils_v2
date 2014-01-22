<?php

require_once 'classes/contribution.php';

class AutreTemplate extends Contribution {
    /**
     * Fichier Temporaire qui permettra de charger le formulaire.
     * @var FichierTMP
     */
    private $fichier_tmp = null;
    
    /**
     * Slug du fichier TMP de la demande qui a servi à charger le formulaire
     * @var String
     */
    private $slug_tmp = null;
    
    public function __construct($slug){
        $mode = -1;
        $slug = "";
        $fichier = null;
        $slug_tmp = null;
        
        //Si validation du formulaire, les infos de la demande se trouvent dans la session
        if($_POST && SiteSession::isExist("approuver_mode")){
            //Lecture dans la session
            $mode = SiteSession::getVariable("approuver_mode");
            $slug_tmp = SiteSession::getVariable("approuver_slug");
            
            //Puis suppression
            SiteSession::supprime("approuver_slug");
            SiteSession::supprime("approuver_mode");
        }else{
            //Lecture des demandes en attente
            $tmp_demandes = new Demandes();
            $demandes = $tmp_demandes -> getDemandes();
            $_POST = null;
            
            //Si nous en avons...
            if(count($demandes) > 0){
                //Lecture du fichier TMP de la demande la plus ancienne
                $fichier = new FichierTMP($demandes[0]);
                
                //On récupère le slug parent ou courant (si page à modifier)
                $slug = $fichier -> getParam("slug");

                //On récupère le mode
                $mode = $fichier -> getParam("mode");

                //On oublie le statut de la page précédente (en attente ou pas)
                SiteSession::supprime("enAttente");

                //Par contre, on doit mémoriser les infos de la demande
                SiteSession::setVariable("approuver_slug", $fichier ->getNom());
                SiteSession::setVariable("approuver_mode", $mode);
            }
        }
		
        //On stock le fichier TMP
        $this -> fichier_tmp = $fichier;
        $this -> slug_tmp = $slug_tmp;
        
        parent::__construct($mode, $slug);
        
		//Si l'Utilisateur n'est pas approbateur, on force la redirection vers l'accueil
		if(!$this -> user -> estApprobateur()){
			$this -> page -> setSlug("");
			$this -> accèsAutorisé();
		}
		
		$this -> modeApprobation = true;
		
		//On initialise de nouveau notre objet (afin de forcer le changement de login et de mail).
		$this -> initialiser();
    }
    
	protected function accèsAutorisé(){
		//Si la page est introuvable, c'est probablement parce que la page parente a été refusée. Il faut donc supprimer aussi cette demande.
		if(!$this -> page -> existe())$this -> fichier_tmp -> supprimerFichier();
		
		parent::accèsAutorisé();
	}
	
    protected function avantChargement() {
        //Chargement des données à partir du fichier TMP
        if(!$_POST && $this -> fichier_tmp != null){
            //Lecture des données du formulaire
            $this -> type = $this -> fichier_tmp -> getParam("type");
            $this -> login = $this -> fichier_tmp -> getParam("login");
            $this -> email = $this -> fichier_tmp -> getParam("mail");
            $this -> titre = $this -> fichier_tmp -> getParam("titre");
            $this -> spoil = $this -> fichier_tmp -> getContenu();

            //Informations sur la demande, à afficher dans la page
            $this -> tpl -> assign( "demande_date", $this -> fichier_tmp -> getParam("date") );
            $this -> tpl -> assign( "demande_ip", $this -> fichier_tmp -> getParam("ip") );
            $this -> tpl -> assign( "demande_url", $this -> page -> générerLien() );
            
            //Type de la demande
            switch ($this -> mode) {
                case 0:
                    $mode = "Modification";
                    break;
                
                case 1:
                    $mode = "Nouvelle page";
                    break;
                
                case 2:
                    $mode = "Nouvelle catégorie";
                    break;
                
                default:
                    $mode = "";
                    break;
            }
            $this -> tpl -> assign( "demande_mode", $mode );

            //Suppression du fichier temporaire
            $this -> fichier_tmp -> supprimerFichier();
        }
        
        parent::avantChargement();
    }
    
    protected function messageErreur($texte) {
        //Il y a eu une erreur, il faut mémoriser la demande, afin de pouvoir s'en resservir
        SiteSession::setVariable("approuver_mode", $this -> mode);
        SiteSession::setVariable("approuver_slug", $this -> slug_tmp);
        
        //Affichage du message d'erreur
        parent::messageErreur($texte);
    }
    
    protected function lectureFichier() {
        switch ($this -> mode) {
            case 0: //Modification
                //Le fichier à modifier est celui en cours
                $fichier = $this -> page -> getFichier();

                $this -> slug_contribution = $this -> page -> getSlug();

                break;

            case 1: //Création d'un fichier
                $fichier = new Fichier($this -> slug_contribution = $this -> page -> getSlug() . "/" . $_POST['slug']);
                break;

            case 2: //Création d'une catégorie
                $this -> slug_contribution = $this -> page -> getSlug() . "/" . $_POST['slug'];
                $fichier = new Fichier($this -> slug_contribution . "/" . FOLDER_INDEX);
                break;
        }
        
        return $fichier;
    }
    
    /**
     * Ouverture d'un sujet de discussion.
     * @param Fichier $fichier Objet du fichier nouvellement créé
     * @param integer $type Type de topic à créer : POST_NORMAL, POST_STICKY, POST_ANNOUNCE ou POST_GLOBAL.
     * @param integer $forum_id Forum qui recevra le nouveau topic.
     */
	private function CréerTopic($fichier, $type = POST_NORMAL, $forum_id = -1){
		//Inutile de créer quoi que ce soit sur le forum, qui n'existe pas.
		if(Passerelle::ExisteForum()){
			//Soit le forum parent nous est fourni, soit, il s'agit de la catégorie parente et on récupère donc le forum de cette dernière.
			$forum_id = $forum_id == -1 ? $this -> page -> getForum() : $forum_id;
			
			//Autres informations nécessaires
			$current_time = time();
			$titre = $this -> page -> générerTitre($fichier -> getParam("titre"));
			$contenu = "Ouverture des critiques/commentaires de la page/catégorie suivante : [url]" . SITE_REEL . "/" . $this -> slug_contribution . "/[/url].\n\nLâchez-vous, mais n'en abusez pas !";
			
			//Création du Topic
			$topic_id = Passerelle::créerTopic($forum_id, $this -> login, $titre, $contenu, $current_time, $type);
			$fichier->setParam('topic', $topic_id);		
		}
	}
	
    /**
     * Création d'un sous-forum dans PHPBB
     * @param Fichier $fichier Objet du fichier nouvellement créé
	 * @return Integer ID du forum nouvellement créé
     */
    private function CréerForum($fichier){
		global $types_masquer;
		
		$parent_id = $this -> page -> getForum();
		$titre = $this -> page -> générerTitre($fichier -> getParam("titre"));
		$contenu = "Lien [url=" . SITE_REEL . "/" . $this -> slug_contribution . "/]vers la page du site[/url].";
		
		//Création du forum (si un forum phpBB a été installé).
		$forum_id = Passerelle::ExisteForum() ? Passerelle::créerForum($parent_id, $titre, $contenu, in_array($this -> page -> getTypePages(), $types_masquer) ? 1 : 0) : -1;
		$fichier -> setParam('forum', $forum_id);
		
		return $forum_id;
    }
    
    protected function CréerValider($fichier) {
        //En mode création, le fichier ne doit pas déjà exister
        if($this -> mode > 0 && $fichier -> existe()){
            $this ->messageErreur("Le slug saisi existe déjà, pour cette catégorie.");
        }else{
            //Cas où nous avons affaire à un nouveau contributeur, il faut alors l'ajouter dans la liste
			$this -> contributeurs -> NouveauContributeur($this -> login, $this -> email, $this -> user -> estIdentifié() ? 1 : 0);
			
			//Initialisation de la liste des contributeurs du fichier en cours (vide si nouveau, sinon liste déjà existante)
			$contributeurs = "";
			if($this -> mode == 0)$contributeurs = ($fichier -> getParam("contributeurs") == "" ? 0 : $fichier -> getParam("contributeurs")) . ", ";
			
			//Mise à jour des contributeurs dans le fichier avec l'ID du demandeur en plus (en cas de modification, sinon c'est le 1er ID).
			$fichier -> setParam("contributeurs", $contributeurs . $this -> contributeurs -> getPosition($this -> login));
			
            //En mode création (page ou catégorie), il y a d'autres traitements à faire...
			if($this -> mode > 0){
				//Dans tous les cas, un topic sera créé. Par défaut, il le sera dans le forum de la catégorie courante
				//(une création implique forcément qu'on soit dans une catégorie qui possède forcément un forum associé).
				$forum_id = -1;
				
				//Dans le cas d'une création de catégorie, ce dernier sera un post-it.
				$type = $this -> mode == 1 ? POST_NORMAL : POST_STICKY;
				
				//Cas d'une création de catégorie...
				if($this -> mode == 2){
					//Création du forum pour le contenu de la catégorie
                    $forum_id = $this -> CréerForum($fichier);
					
                    //Création du répertoire FTP
                    mkdir(FOLDER_PAGES . "/" . $this -> page -> getSlug() . "/" . $_POST['slug']);

                    //Définition du types de contenu
					$fichier ->setParam("type", $this -> type);                    
				}
				
				//Création du topic
				$this -> CréerTopic($fichier, $type, $forum_id);
			}
			
            //Enregistrement
            $fichier -> enregistrer();

            //Lecture des demandes afin de les modifier
            $demandes = new Demandes();
            foreach ($demandes -> getDemandes() as $slug) {
                //Lecture du fichier
                $tmp = new FichierTMP($slug);

                //Si la demande en attente a été faite dans la catégorie qui vient d'être validée, il faut changer le slug temporaire par le définitif.
                if($tmp -> getParam("slug") == $this -> slug_tmp){
                    $tmp -> setParam("slug", $this -> slug_contribution);
                    $tmp -> enregistrer();
                }
            }

            //On prévient l'utilisateur en lui envoyant un mail (sauf si le demandeur est l'admin) que sa demande a été validée
            if($this -> user -> getLogin() != $this -> login){
                $contenu = "Titre : " . $this -> titre . "\nLien vers la page : " . SITE_REEL . "/" . $this -> slug_contribution . "/";
                envoyerMail("Demande de " . ($this -> mode > 0 ? "création" : "modification") . " de " . ($this -> mode > 1 ? "catégorie" : "page") . " validée", $contenu, $this -> email);
            }

            //Affichage du succès.
            $this -> msg_text = ($this -> mode > 0 ? "Création" : "Modification") . " effectuée avec succès.";
        }
    }
}

?>
