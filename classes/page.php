<?php

class Page {
    /**
     * La page est une simple page ? La page d'introduction d'une catégorie ? :
     * <br />0 : Introuvable
     * <br />1 : C'est une page
     * <br />2 : C'est une catégorie
     * @var Integer
     */
    private $typePage = 0;
    
    /**
     * La page en cours hérite des types des catégories parentes.
     * @var Array Liste des types en provenance des pages parentes (présentation de catégories)
     */
    private $typesDossier = array();
    
    /**
     * Fichier de données de la page
     * @var Fichier Instance de la classe Fichier
     */
    private $fichier;
    
    /**
     * Type de contenu de la page en cours
     * @var Integer Numéro de type de la page en cours.
     */
    private $typeContenu = -2;
    
    /**
     * Raccourcis vers la page en cours.
     * @var String Raccourcis.
     */
    private $slug = "";
    
    /**
     * ID du forum de la catégorie parente.
     * @var Integer ID du forum parent.
     */
	private $forum_parent = -1;
	
    private $enAttente = false;
    
    /**
     * Objet Page pour une page simple ou une catégorie.
     * @param String $slug Slug de la page en cours
     * @param Boolean $enAttente Permet de spécifier que la page est en attente d'approbation.
     */
    public function __construct($slug, $enAttente = false) {
        $this -> slug = $slug;
        $this -> enAttente = $enAttente;
        
        //Lecture du fichier
        $this ->lireFichier();

        //Lecture des fichiers parents
        $this ->lireTypes();
    }
    
    /**
     * Création d'un objet Fichier correspondant à la page en cours.
     */
    private function lireFichier(){
        //Page en attente d'approbation ? Le fichier à lire n'est donc pas au même endroit
        $slug = ($this -> enAttente ? "../tmp/" : "") . $this -> slug;
        
        //On commence par supposer que le slug concerne une page.
        $this -> fichier = new Fichier($slug == "" ? FOLDER_INDEX : $slug, true);
        
        //Le fichier n'existe pas...
        if(!$this -> fichier -> existe()){
            //Il s'agit alors d'une catégorie (d'un répertoire)
            $this -> fichier = new Fichier($slug . "/" . FOLDER_INDEX, true);
            
            //Si introuvable malgré tout...
            if(!$this -> fichier -> existe()){
                //Erreur 404
                $this -> fichier = new Fichier("404", true);
            }else $this -> typePage = 2;
        }else $this -> typePage = 1;
        
        //Si nous sommes en train de lire une page en attente d'approbation, le type se trouve renseigné dans le fichier
        if($this -> enAttente)$this -> typePage = $this -> fichier -> getParam("mode");
    }
    
    /**
     * Permet, en lisant les fichiers parents, de récupérer les types de dossier qu'ils acceptent.
     * <br />En effet, les pages héritent des types de ses parents.
     */
    private function lireTypes(){
        global $types;
        
        //Conversion en tableau
        $noms = explode("/", $this -> slug);
        
        //On construit le slug parent à partir du slug du fichier en cours
        $slug = "";
        $type = -1;
        foreach ($noms as $nom) {
            //Construction du slug parent
            $slug .= ($slug == "" ? "" : "/") . $nom;
            
            //Seuls les slug parent (pas le thème) nous intéressent.
            if($slug != $this -> slug){
                //Lecture du fichier
                $parent = new Page($slug);
                
                //Si un type de dossier avait été précédemment défini, on l'utilise avec ce parent là
                if($type >= 0)$this -> typesDossier[$types[$type]] = array('slug' => $parent ->générerLien(), 'titre' => $parent -> générerTitre());
                
                //Lecture du type de dossier du parent en cours.
                $type = $parent -> getTypePages();
				
				//Lecture du forum du parent
				$this -> forum_parent = $parent -> getForum();
            }
        }
        
        //On récupère le dernier type de fichier lu
        $this -> typeContenu = intval($type);
    }
    
    /**
     * Lecture de toutes les pages simples de la catégorie en cours. Triés sur le titre des pages.
     * @return Page[] Tableau d'objets Page.
     */
    public function lirePages(){
        $pages = array();
        
        //Une page ne peut pas contenir de pages !
        if($this -> estCatégorie()){
            //Nouvelle connexion FTP au répertoire en cours
            $ftp = new FTP($this -> slug);
            
            //On récupère les fichiers du répertoire en cours.
            $slugs = $ftp -> getFichiers();
            
            //On passe en revue tous les slugs pour créer les pages
            foreach ($slugs as $slug) {
                //On ne s'occupe pas de la page de présentation de la catégorie.
                if($slug != $this -> slug . "/" . FOLDER_INDEX){
                    $pages[] = new Page($slug);
                }
            }
            
            //On trie les fichiers sur le titre
            usort($pages,'trier');
        }
        
        return $pages;
    }
    
    /**
     * Lecture de toutes les catégories contenues dans la catégorie en cours. Triés sur le titre.
     * @return Page[] Tableau d'objets Page.
     */
    public function lireCatégories(){
        $catégories = array();
        
        //Une page ne peut pas contenir de pages !
        if($this -> estCatégorie()){
            //Nouvelle connexion FTP au répertoire en cours
            $ftp = new FTP($this -> slug);
            
            //On récupère les répertoires du répertoire en cours.
            $slugs = $ftp -> getDossiers();
            
            //On passe en revue tous les slugs pour créer les pages
            foreach ($slugs as $slug) {
                $catégories[] = new Page($slug);
            }
            
            //On trie les fichiers sur le titre
            usort($catégories,'trier');
        }
        
        return $catégories;
    }
    
    /**
     * Le fichier est-il verrouillé en modification ? Pour le savoir, il suffit de regarder si une demande est en attente.
     * @return Boolean Vrai si le fichier est verrouillé. Faux, sinon.
     */
    public function estBloqué(){
        $bloqué = false;
        
        //Lecture des demandes
        $demandes = new Demandes();
        
        //Passage en revue des demandes
        foreach ($demandes -> getDemandes() as $slug) {
            //Lecture du fichier TMP correspondant
            $tmp = new FichierTMP($slug);
            
            //Si le fichier en cours concerne le slug en cours, c'est qu'une demande est en attente -> Bloqué
            if(($tmp -> getParam("slug") == $this -> slug) &&($tmp -> getParam("mode") == 0)){
                $bloqué = true;
                break;
            }
        }
        
        return $bloqué;
    }
    
    /**
     * Génère un lien pour la page en cours.
     * @param Integer $num_pagination Numéro de page demandé (pagination);
     * @return string Lien HTML.
     */
    public function générerLien($num_pagination = 1){
        //Pas de pagination pour les simples pages
        if($this -> estCatégorie() && $num_pagination > 1){
            $pagination = ";$num_pagination";
        }else $pagination = "";

        return "/" . $this -> slug . $pagination . "/";
    }

    /**
     * Retourne le titre brut contenu dans le fichier (sans aucun formatage ni ajout de texte).
     * @return String Titre brut lu.
     */
    public function getTitre(){
        return $this -> fichier -> getParam("titre");
    }
    
    /**
     * Permet de récupérer les paramètres du fichier.
     * @return String[] Paramètres du fichier.
     */
    public function getParam($nom = ""){
        return $this -> fichier ->getParam($nom);
    }
    
    /**
     * Permet de récupérer l'ID du forum correspondant à la page en cours.
     * @return Integer ID du forum ou -1.
     */
    public function getForum(){
        return $this -> fichier -> getParam("forum") == "" ? -1 : intval($this -> fichier -> getParam("forum"));
    }
    
    /**
     * Permet de récupérer l'ID du forum parent.
     * @return Integer ID du forum ou -1.
     */
	public function getForumParent(){
		return $this -> forum_parent;
	}
	
    /**
     * Permet de récupérer l'ID du topic correspondant à la page en cours.
     * @return Integer ID du topic ou -1.
     */
    public function getTopic(){
        return $this -> fichier -> getParam("topic") == "" ? -1 : intval($this -> fichier -> getParam("topic"));
    }
    
    /**
     * Si aucun titre n'est fourni, on génère un titre à partir de la page en cours et du type de contenu qu'il est.
	 * <br />Si un titre est fourni, on génère un titre à partir de ce paramètre et du type de contenu des pages possédées par la page en cours.
	 * 
	 * $param String $titre Titre optionnel
     * @return string Titre du fichier en cours (ou fourni).
     */
    public function générerTitre($titre = ""){
        global $types, $types_masquer;
        $préfixe = "";
        
		if($titre == ""){
			$titre = $this -> getTitre();
			$type = $this -> typeContenu;
		}else $type = $this -> getTypePages();
		
        //Lecture du préfixe à ajouter dans le titre
		if($type >= 0 && !in_array($type, $types_masquer))$préfixe = $types[$type];
        
        return ($préfixe != "" ? $préfixe . ' ' : '') . $titre;
    }
    
    /**
     * Retourne le titre du fichier en cours, avec, en préfixe, les types hérités par ses parents + le type de page.
     * @return String Titre Complet.
     */
    public function générerTitreComplet(){
        $titre = "";
        
        if(count($this -> typesDossier) > 0){
            foreach ($this -> typesDossier as $tabValeur) {
                $titre .= ($titre == '' ? '' : " - ") . $tabValeur['titre'];
            }
            
            $titre .= " - " . $this -> générerTitre();
        }else $titre = $this -> générerTitre();
        
        return $titre;
    }
    
    /**
     * Permet de récupérer le contenu du fichier en cours et de le formatter en HTML afin qu'il puisse être affiché dans une page.
     * @param Boolean $HTML Faux, pour récupérer le contenu brut. Vrai, pour récupérer le contenu formatté en HTML.
     * @return String Contenu du fichier.
     */
    public function getContenu($HTML = true){
        $débutParagraphe = true;
        $ListeEnCours = false;
        $contenu = $this -> fichier -> getContenu ();
        
        if($HTML){
            //On va passer en revue toutes les lignes afin de s'occuper des paragraphes et des retours à la ligne.
            $lignes = explode("\n", $contenu);
            for($x = 0; $x < count($lignes); $x++){
                $ligne = trim($lignes[$x]);

                //Demande d'un nouveau paragraphe. Il faut donc, au préalable, clôturer une liste éventuellement ouverte.
                if($ligne == ""){
                    $lignes[$x] = ($ListeEnCours ? "</ul>" : "") . "</p><p>";
                    $ListeEnCours = false;
                    $débutParagraphe = true;
                }else{
                    //Une liste est ouverte : il faut, soit la cloturer, soit ajouter un élément.
                    if($ListeEnCours){
                        //On ajoute un élément à la liste
                        if(preg_match("/^- (.*)?/i", $ligne, $reste)){
                            $lignes[$x] = "<li>" . $reste[1] . "</li>";
                        }else{ //On cloture la liste
                            $lignes[$x] = "</ul></p><p>" . $lignes[$x];
                            $ListeEnCours = false;
                        }
                    }else{ //Pas de liste ouverte : il faut soit en ouvrir une, soit aller à la ligne.
                        //Ouverture d'une liste
                        if(preg_match("/^- (.*)?/i", $ligne, $reste)){
                            $lignes[$x] = "<ul class=\"list-red\"><li>" . $reste[1] . "</li>";
                            $ListeEnCours = true;
                        }elseif($débutParagraphe){
                            $lignes[$x] = $lignes[$x];
                            $débutParagraphe = false;
                        }else $lignes[$x] = "<br />" . $lignes[$x];
                    }
                }
            }

            //Conversion en chaîne
            $contenu = implode("\n", $lignes);

            //Mise en forme des titres
            $contenu = str_replace("<p>\n[", "\n<h2>", $contenu);
            $contenu = str_replace("[", "<h2>", $contenu);
            $contenu = str_replace("]\n</p>", "</h2>\n", $contenu);
            $contenu = str_replace("]", "</h2>", $contenu);
        }

        return $contenu;
    }
    
    /**
     * Permet de résumer le contenu du fichier. Il faut donc avoir lu le contenu !
     * @return String Contenu résumé.
     */
    public function getRésumé(){
        //On supprime les retours à la ligne et les espaces en trop
        $contenu = preg_replace("/[ ]+/i", " ", str_replace("\n", " ", $this -> fichier -> getContenu()));

        return résumer(TAILLE_RESUME, $contenu);
    }
    
    /**
     * Permet de récupérer la catégorie mère de la page en cours.
     * <br />Sert essentiellement pour mettre en évidence le lien du thème.
     * @return string Nom du répertoire racine.
     */
    public function getRacine(){
        //Qu'on transforme ensuite en tableau
        $slugs = explode("/", $this -> slug);
        
        //Le slug racine est le premier élément de notre tableau
        return ($slugs[0] == $this -> slug ? "" : $slugs[0]);
    }
    
    /**
     * Permet de récupérer le type de fichier des parents du fichier en cours.
     * @return Array Tableau associatif de tableau où, pour chaque élément, nous retrouvons le lien et le titre du fichier d'où provient cet élément.
     */
    public function getTypesDossier(){
        return $this -> typesDossier;
    }
    
    /**
     * Retourne le fichier associé à la page en cours.
     * @return Fichier
     */
    public function getFichier(){
        return $this -> fichier;
    }
    
    public function getSlug(){
        return $this -> slug;
    }
    
    /**
     * Permet de savoir si le fichier associé à la page en cours existe ou non.
     * @return Boolean Vrai si le fichier existe. Faux, sinon.
     */
    public function existe(){
        return $this -> typePage > 0 ? true : false;
    }

    /**
     * Permet de savoir si la page en cours est la présentation d'une catégorie
     * @return Boolean Vrai si la page est une catégorie ou faux si c'est une page simple (ou page inexistante)
     */
    public function estCatégorie(){
        return $this -> typePage > 1 ? true : false;
    }
    
    /**
     * Permet d'effectuer des modifications spéciales afin de définir la page en tant que Catégorie Spéciale.
     */
    public function estCatégorieSpéciale(){
        //On précise que la page comportera une liste de fichiers à afficher.
        $this -> typePage = 2;
    }
    
    /**
     * Permet de savoir si la page en cours est la page d'accueil du site.
     * @return Boolean Vrai si nous sommes à la page d'accueil du site. Faux, sinon.
     */
    public function estPageAccueil(){
        return $this -> slug == "" ? true : false;
    }
    
    /**
     * Permet d'effectuer des modifications spéciales pour les pages spéciales (qui ne sont pas des fichiers mais des classes).
     */
    public function estPageSpéciale($slug, $titre, $contenu){
        //Création d'un nouvel objet Fichier
        $fichier = new Fichier($slug);
        
        //On change le titre si défini
        if($titre != "")$fichier -> setParam("titre", $titre);
        
        //On change le contenu, si défini
        if($contenu != "")$fichier -> setContenu($contenu);
        
        //On reporte les modifications sur la page en cours.
        $this -> slug = $slug;
        $this -> fichier = $fichier;
    }
    
    /**
     * Permet de savoir si la page en cours est actuellement en attente d'approbation.
     * @return Boolean Vrai si la page est en attente d'approbation. Faux, sinon.
     */
    public function estEnAttente(){
        return $this -> enAttente;
    }
    
    /**
     * Dans le cas de catégories, sert à connaître le type de contenu de cette dernière.
     * @return Integer Type des pages (-2 s'il n'y a pas de type (cas pour une simple page) et -1 pour une catégorie sans type de contenu).
     */
	public function getTypePages(){
		return $this -> fichier -> getParam('type') == "" ? -2 : intval($this -> fichier -> getParam('type'));
	}
    
    /**
     * Retourne la liste des contributeurs sous forme d'un tableau.
     * @return Array Liste des contributeurs
     */
    public function getArrayContributeurs(){
        return explode(", ", ($this -> fichier -> getParam("contributeurs") == "" ? "0" : $this -> fichier -> getParam("contributeurs")));
    }
    
    /**
     * Génère une liste de contributeurs, sans doublons, afin d'être affichée dans la page.
	 * @param Contributeurs $liste_contrib Objet Contributeurs qui permettra de convertir les ID en login.
     * @return String Liste sérialiée des contributeurs, sans doublons.
     */
    public function getStringContributeurs($liste_contrib){
        $contributeurs = array();
        
        foreach ($this -> getArrayContributeurs() as $contributeur_id) {
			//A partir de l'ID, on récupère le login
			$login = Passerelle::générerLienProfil($liste_contrib->getLogin($contributeur_id));
			
            if(!in_array($login, $contributeurs))$contributeurs[] = $login;
        }
        
        return implode(", ", $contributeurs);
    }
    
    public function setSlug($slug){
        $this -> slug = $slug;
    }
}

?>
