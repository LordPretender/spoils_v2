<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct("");
        
        //Définition du template pour le contenu
        $this -> gabarit = $slug;
        
        //Conversion en page spéciale
        $this -> page ->estPageSpéciale($slug, PAGE_DEMANDES, "Listing des demandes en attente d'approbation.");
		
		//Accès limité aux approbateurs
		$this -> ApprobateurRequis();
    }
    
    protected function avantChargement(){
		$datas = array();
		
        //Lecture des demandes
        $demandes = new Demandes();
        
        //Si nous en avons... Ajout du lien des demandes
        if($demandes -> existeDemandes()){
			//Lecture des demandes
			foreach($demandes -> getDemandes() as $key => $slug){
				//Lecture du fichier TMP
				$fichier_tmp = new FichierTMP($slug);
				
				$datas[] = $fichier_tmp->getParam();
				$compteur = count($datas);
	            
	            //Type de la demande
	            switch ($datas[$compteur-1]["mode"]) {
	                case 0:
	                    $datas[$compteur-1]["mode"] = "Modification";
	                    break;
	                
	                case 1:
	                    $datas[$compteur-1]["mode"] = "Nouvelle page";
	                    break;
	                
	                case 2:
	                    $datas[$compteur-1]["mode"] = "Nouvelle catégorie";
	                    break;
	            }			
			}
			
			$this -> tpl -> assign( "demandes", $datas );
		}
    }
}

?>
