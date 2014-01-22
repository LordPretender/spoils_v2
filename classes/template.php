<?php

require_once 'includes/rain.tpl.class.php';

class SiteTemplate {
    /**
     * Instance de la classe RainTPL, moteur de template utilisé.
     * @var RainTPL
     */
    protected $tpl;
    /**
     * Chaîne du nom du gabarit à appeler pour ajouter le contenu au template.
     * @var String
     */
    protected $gabarit = "page";
    
    /**
     * Instance de la classe Page qui lira le bon fichier en fonction d'une page simple ou de type catégorie.
     * @var Page
     */
    protected $page;
    
    /**
     * Comporte une liste de lien (avec son titre) à afficher dans le menu latéral, partie Navigation.
     * @var Array Tableau de liste de liens.
     */
    private $navigation = array();
    
    /**
     * Comporte une liste de lien (avec son titre) à afficher dans le menu latéral, partie Approbateur.
     * @var Array Tableau de liste de liens.
     */
    private $module_approbateur = array();
    
    /**
     * Instance de la classe Contributeurs qui, comme son nom l'indique, comporte l'ensemble des contributeurs.
     * @var Contributeurs
     */
	protected $contributeurs;
	
    /**
     * Comporte une liste de liens (avec son titre) à afficher dans le menu latéral, partie partenaires.
     * @var Array
     */
    private $partenaires = array();
    
    /**
     * Permet de savoir si la page à afficher nécessite une identification ou non.
     * @var Boolean
     */
    private $pagePrivée = false;
    
    /**
     * Permet d'ajouter du texte personnalisé dans le titre du fichier (META TITLE)
     * @var String
     */
    protected $titre_suffixe = "";

    /**
     * Permet d'ajouter du texte personnalisé avant de la description du fichier (META DESCRIPTION)
     * @var type 
     */
    protected $préfixe_description = "";
    
    protected $user;
    
    /**
     * Initialisation du template HTML qui sera affiché à l'utilisateur
     * @param String $slug
     * @param Boolean $enAttente Permet de spécifier que la page est en attente d'approbation.
     */
    public function __construct($slug, $enAttente = false){
		//Lecture des contributeurs
		$this -> contributeurs = new Contributeurs();
		
		//Initialisation d'utilisateur
        $this -> user = Passerelle::getUtilisateur();
        
        //Chargement + configuration du moteur de template
        $this -> initMoteurTPL();
        
        //Lecture des données
        $this -> page = new Page($slug, $enAttente);
        
        //Chargement des variables utilisés pour le contenu du HEAD.
        $this -> initHead();
        
        //lecture des thèmes pour charger le menu
        $this -> initThèmes();
    }
    
    /**
     * Lecture des thèmes du site puis initialisation de la variable TPL.
     */
    protected function initThèmes(){
        $thèmes = array();
        
        //Lecture des répertoires à la racine
        $ftp = new FTP();
        $slugs = $ftp -> getDossiers();
        
        //Passage en revue des slugs
        foreach ($slugs as $slug) {
            //Lecture du fichier correspondant
            $fichier = new Fichier($slug . "/" . FOLDER_INDEX, false);
            
            $thèmes["$slug"] = $fichier -> getParam("titre");
        }
        
        //Tri sur le titre
        asort($thèmes);
        
        //Les thèmes
        $this -> tpl -> assign( "themes", $thèmes );
        
        //Thème en cours
        $this -> tpl -> assign( "fichier_racine", $this -> page -> getRacine() );
    }
    
    /**
     * Chargement des variables utilisées pour la partie HEAD du code HTML
     */
    protected function initHead(){
        //Titre et slogan du site
        $this -> tpl -> assign( "site_titre", SITE_NOM );
        $this -> tpl -> assign( "site_slogan", SITE_SLOGAN );
        $this -> tpl -> assign( "site_keywords", SITE_KEYS );
        $this -> tpl -> assign( "site_tpl", SITE_URL . "/" . raintpl::$tpl_dir);
        $this -> tpl -> assign( "site_url", SITE_URL);
        $this -> tpl -> assign( "visiteur", !$this -> user -> estIdentifié());
        $this -> tpl -> assign( "approbateur", $this -> user -> estApprobateur());
    }

    /**
     * Chargement du moteur de template
     */
    protected function initMoteurTPL(){
        raintpl::$tpl_dir = "html/"; // template directory
        raintpl::$cache_dir = "tmp/"; // cache directory
        raintpl::configure( 'path_replace', false );
        
        $this -> tpl = new raintpl(); //instance de classe
    }

    /**
     * Méthode à utiliser pour ajouter d'autres variables nécessaires dans les classes filles.
     */
    protected function avantChargement(){
        $nombrePagination = 1;
        $pagination = isset($_GET['param']) ? intval($_GET['param']) : 1;

        $contenu = array();
        
        if($this -> page -> estPageAccueil()){
            //Lecture des dernières contributions
            $tmp = new Fichier("../modifications");
            foreach ($tmp -> getParam() as $lien_titre => $résumé) {
                //On récupère le lien et le titre
                $params = explode(PARAM_SEPARATEUR_VALEUR, $lien_titre);

                $contenu[] = array('titre' => $params[1], 'lien' => $params[0], 'description' => $résumé);
            }
        }else{
            //On récupère en 1er les catégories
            $pages = $this -> page -> lireCatégories();

            //Si nous n'avons pas de catégories, on tente de regarder s'il y a des pages simples
            if(count($pages) == 0)$pages = $this -> page -> lirePages();

            //Si nous avons des pages à afficher (pages simples ou catégories)
            if(count($pages) > 0){
                //Nombre total de pages de la pagination
                $nombrePagination = ceil(count($pages)/PAGINATION);

                //Retour au début si le numéro demandé est supérieur au nombre total
                if($pagination > $nombrePagination)$pagination = 1;

                //On passe maintenant en revue toutes les fichiers récupérées (en ne gardant que les pages qui doivent être affichées en fonction du numéro de pagination fourni).
                for($x = (max(($pagination - 1) * PAGINATION, 0)); $x < (min($pagination * PAGINATION,count($pages))); $x++){
                    $contenu[] = array('titre' => $pages[$x] -> générerTitre(), 'lien' => $pages[$x] -> générerLien(1), 'description' => $pages[$x] -> getRésumé());
                }

                //Génération de la pagination
                $this -> tpl -> assign( "pagination", paginer($nombrePagination, $pagination, $this -> page) );
            }
            
            //Infos supp
            $this -> tpl -> assign( "info_supp", $this -> page -> getTypesDossier());
        }

        $this -> tpl -> assign( "pages", $contenu );
    }
    
    /**
     * Permet de définir une variable utilisable dans le moteur de template.
     * @param String $clef Nom de la variable qui sera utilisable dans de l'HTML.
     * @param Object $valeur Contenu de la variable.
     */
    public function Définir($clef, $valeur){
        $this -> tpl -> assign( $clef, $valeur );
    }

    /**
     * Lecture du fichier des dernières modifications afin de lire les X dernières modifications pour renseigner le bloc "Changements"
     * @return array Tableau de liens.
     */
    private function DernièresModifications(){
        $récents = array();
        $last = new Fichier("../modifications", false);
        
        //Lecture des X dernières modifications
        foreach ($last -> getParam() as $lien_titre => $résumé) {
            //On récupère le lien et le titre
            $params = explode(PARAM_SEPARATEUR_VALEUR, $lien_titre);

            $récents[$params[0]] = $params[1];
        }
        
        return $récents;
    }
    
    /**
     * Lecture des demandes en attente dans la catégorie en cours
     * @return array Tableau de liens.
     */
    private function Demandes(){
        $demandes = array();
        
        //Lecture des demandes
        $tmp_demandes = new Demandes();
        
        //Passage en revue des demandes
        foreach ($tmp_demandes -> getDemandes() as $path_demande) {
            //Lecture du fichier
            $fichier = new FichierTMP($path_demande);

            //On ne garde que les demandes de création de catégories faites dans la catégorie en cours
            if($fichier -> getParam("mode") == 2 && $fichier -> getParam("slug") == $this -> page -> getSlug())$demandes["/demande;$path_demande/"] = $this -> getPage() -> générerTitre($fichier -> getParam("titre"));
        }

        return $demandes;
    }
    
    /**
     * Lecture des statistiques puis sérialisation et formatage en HTML
     * @return String Chaîne HTML qui comporte les stats.
     */
    private function Statistiques(){
        $tmp = array();
        
        //Lecture des stats
        $fichier = new Fichier("../statistiques");
        
        //Sérialisation des clefs et des valeurs
        foreach ($fichier -> getParam() as $Label => $compteur) {
            $tmp[] = "$Label : $compteur";
        }
        
        //Sérialisation de l'ensemble
        $stats = implode("<br />", $tmp);
        
        return $stats;
    }
    
    /**
     * Permet de gérer un slug d'une page collaborative en fonction du mode renseigné
     * @param Integer $mode Type de slug collaboratif.
     * @return String Slug collaboratif demandé.
     */
    public function slugCollaboratif($mode){
        $slug_collaboratif = "";
        
        switch ($mode) {
            case 0: //Modification
                $slug_collaboratif = "modifier-page";
                break;

            case 1: //Création de fichier
                $slug_collaboratif = "creer-page";
                break;

            case 2:
                $slug_collaboratif = "creer-categorie";
                break;
        }
        
        return $slug_collaboratif;
    }
    
    /**
     * Permet d'ajouter un lien dans le menu latéral, partie navigation.
     * @param String $titre Titre du lien (ou un tableau de 2 éléments qui contient le slug puis le titre)
     * @param String $slug Slug du fichier qui sera utilisé pour générer le lien (ou vide)
     */
    public function LienNavigation($titre, $slug = ""){
		if($slug != ""){
	        $this -> navigation[] = array('slug' => "/$slug/", 'titre' => $titre);
		}elseif(is_array($titre))$this -> navigation[] = array('slug' => "/" . $titre[0] . "/", 'titre' => $titre[1]);
    }
    
    /**
     * Permet d'ajouter un lien dans le menu latéral, partie Approbateur.
     * @param String $titre Titre du lien (ou un tableau de 2 éléments qui contient le slug puis le titre)
     * @param String $slug Slug du fichier qui sera utilisé pour générer le lien
     */
    private function LienApprobateur($titre, $slug){
        $this -> module_approbateur[] = array('slug' => "/$slug/", 'titre' => $titre);

    }
    
    /**
     * Permet d'ajouter un lien dans le menu latéral, partie partenaires.
     * @param String $titre Titre du lien
     * @param String $url URL du site du partenaire.
     */
    public function LienPartenaire($titre, $url){
        $this -> partenaires[] = array('url' => $url, 'titre' => $titre);
    }
    
    private function LienDemandes(){
        //Affichage ou non du lien pour gérer les demandes, si identifié.
        if($this -> user -> estApprobateur()){
            //Lecture des demandes
            $demandes = new Demandes();
            
            //Si nous en avons... Ajout du lien des demandes
            if($demandes -> existeDemandes()){
				$this -> LienApprobateur(PAGE_DEMANDES, "demandes");
				$this -> LienApprobateur("Approuver une demande", "approuver");
			}
        }
    }
    
	public function getUser(){
		return $this -> user;
	}
	
    /**
     * Permet d'accéder à la page en cours où l'on va afficher le contenu.
     * @return Page Instance de l'objet de la page associée.
     */
    public function getPage(){
        return $this -> page;
    }
    
    /**
     * La page est verrouillée à la modification ?
     * @return Boolean
     */
    protected function pageBloquée(){
        return $this -> page -> estBloqué() && !$this -> user -> estApprobateur();
    }
    
    /**
     * Méthode appelée pour charger les liens de collaboration dans le menu de navigation.
     */
    public function ajouterLiensCollaboration(){
		global $types_catégories;
		
        //Dans un répertoire, il nous est possible de créer un répertoire ou un fichier (selon les paramètres du fichier de présentation du répertoire).
        if($this -> page -> estCatégorie()){
			if(in_array($this -> page -> getTypePages(), $types_catégories)){
				$this -> LienNavigation(PAGE_CAT_CREER_TITRE, $this -> slugCollaboratif(2));
			}else $this -> LienNavigation(PAGE_PAGE_CREER_TITRE, $this -> slugCollaboratif(1));
        }

        //Dans tous les cas, on doit pouvoir modifier toutes les pages.
        if($this -> page -> getSlug() != ""){
            if($this -> pageBloquée()){
                $this -> LienNavigation("[Modification en cours]", $this -> page ->getSlug());
            }else $this -> LienNavigation(PAGE_PAGE_MODIFIER_TITRE, $this -> slugCollaboratif(0));
        }
    }
    
    /**
     * Méthod permettant de rediriger vers l'accueil si l'utilisateur n'est pas approbateur.
     */
	public function ApprobateurRequis(){
		if(!$this -> user -> estApprobateur()){
			header('Location: /');
			die;
		}
	}
	
    /**
     * Méthode finale qui permet de charger la page à partir du fichier fourni avec les variables définies.
     */
    public function Charger(){
        //Gestion de la balise TITLE
        $html_titre = $this -> page -> générerTitreComplet() . ($this -> titre_suffixe != "" ? " - " . $this -> titre_suffixe : '');
        $this -> tpl -> assign( "meta_title", SITE_NOM . ($html_titre == "" ? "" : " | " . $html_titre) );
        
        //Gestion de la balise DESCRIPTION
        $html_desc = ($this -> préfixe_description != "" ? $this -> préfixe_description . "... " : "") . $this -> page -> getRésumé();
        $this -> tpl -> assign( "meta_description", $html_desc );
        
        $this ->avantChargement();
        
        //Ajout dans la navigation du lien pour afficher une demande
        $this -> LienDemandes();
        
        //Variables principales
        $this -> tpl -> assign( "contenu", $this -> gabarit );
        $this -> tpl -> assign( "navigation", $this -> navigation );
        $this -> tpl -> assign( "partenaires", $this -> partenaires );
        if(count($this -> module_approbateur) > 0)$this -> tpl -> assign( "module_approbateur", $this -> module_approbateur );
        $this -> tpl -> assign( "stats", $this -> Statistiques() );
        if(!$this -> page -> estPageAccueil())$this -> tpl -> assign( "changements", $this -> DernièresModifications());
        
        //Gestion du module des demandes
        $demandes = $this -> Demandes();
        if(count($demandes) > 0)$this -> tpl -> assign( "module_demandes", $demandes );
        
        //Variables de fichiers
        $this -> tpl -> assign( "fichier_titre", $this -> page -> générerTitre() );
        $this -> tpl -> assign( "fichier_contenu", $this -> page -> getContenu() );
        $this -> tpl -> assign( "fichier_slug", $this -> page -> getSlug() );
        $this -> tpl -> assign( "fichier_url", $this -> page -> générerLien() );
        $this -> tpl -> assign( "fichier_type", $this -> page ->estCatégorie() );
        $this -> tpl -> assign( "fichier_contributeurs", $this -> page -> getStringContributeurs($this -> contributeurs) );
        
        //Sauvegarde du slug de la page en cours
        $this -> sauverSlug();
        
        //Chargement
        $this -> tpl -> draw("structure");
    }
    
    /**
     * Enregistrement en Session de la page en cours.
     */
    private function sauverSlug(){
        //On sauvegarde le slug
        SiteSession::setVariable("slug", $this -> page -> getSlug());

        //Si la page en cours est en attente d'approbation, on sauvegarde ce statut, sinon on supprime l'ancienne valeur.
        if($this -> page -> estEnAttente()){
            SiteSession::setVariable("enAttente", $this -> page -> getSlug());
        }else SiteSession::supprime("enAttente");
    }
}

?>