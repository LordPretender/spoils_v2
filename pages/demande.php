<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct((isset($_GET['param']) ? $_GET['param'] : ""), true);
		global $types_catégories;
        
        //Il n'est possible de visualiser que les demandes de création de catégorie
        if(!$this -> page ->estCatégorie())header('Location: /');
        
        //Nous sommes censés nous trouver dans une catégorie. Il est donc permis de créer des catégories et des pages.
		if(in_array($this -> page -> getTypePages(), $types_catégories)){
			$this -> LienNavigation(PAGE_CAT_CREER_TITRE, $this -> slugCollaboratif(2));
		}else $this -> LienNavigation(PAGE_PAGE_CREER_TITRE, $this -> slugCollaboratif(1));
        
        $this -> tpl -> assign( "pageSpeciale", 1 );
    }
}

?>
