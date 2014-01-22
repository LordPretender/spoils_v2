<?php

class AutreTemplate extends SiteTemplate {
    public function __construct($slug){
        parent::__construct("../contenus");
        
        $this -> page -> estCatégorieSpéciale();
    }
    
    protected function avantChargement(){
        $estTitreFichier = true;
        
        //Résultats qui seront affichés dans la page.
        $résultats = array();
        
        //On récupère l'expression cherché.
        $expression = trim($_POST && isset($_POST['keyword']) ? $_POST['keyword'] : '');
        
        //Inutile de chercher des résultats si le champ est vide ! Déjà qu'on aura lu le fichier pour rien...
        if($expression != ""){
            //On passe en revue tous les fichiers afin de chercher l'expression dans le contenu de ce dernier.
            foreach ($this -> page -> getParam() as $lien_titre => $contenu) {
                //Le 1er élément est le titre de notre fichier, donc on ignore.
                if(!$estTitreFichier){
                    //On récupère le lien et le titre
                    $params = explode(PARAM_SEPARATEUR_VALEUR, $lien_titre);
                    $lien = $params[0];
                    $titre = $params[1];

                    //Si l'expression est présente dans le contenu ou dans le titre
                    if(stripos(supprimerAccents($titre . " - " . $contenu), supprimerAccents($expression)) !== false){
                        $résultats[] = array('titre' => $titre, 'lien' => $lien, 'description' => résumer(TAILLE_RESUME, $contenu));
                    }
                }
                
                $estTitreFichier = false;
            }
            
            //Ajout au template
            $this -> tpl -> assign( "pages", $résultats );
            $this -> tpl -> assign( "pageSpeciale", 1 );
        }
    }
}

?>
