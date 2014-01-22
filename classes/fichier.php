<?php

class Fichier {
    private $Params; //Tableau qui contiendra toutes les données sauf le contenu
        
    /**
     * Chemin d'accès au fichier FTP
     * @var String
     */
    private $chemin = "";
    
    /**
     * Le fichier existe-t-il ?
     * @var Boolean Vrai s'il existe, faux, sinon.
     */
    private $existe = false;
    
    /**
     * Contenu réel du fichier (ne prend pas en compte les paramètres qu'il y a aussi)
     * @var String 
     */
    private $Contenu;
    
    /**
     * Accède aux données du fichier
     * @param String $slug Chemin relatif sans extension du fichier
     * @param Boolean $avecContenu Vrai (par défaut) pour afficher toutes les données. Faux, pour afficher tout sauf le contenu.
     */
    public function __construct($slug, $avecContenu = true){
        $this -> Params = Array();
        $this -> Contenu = "";
        $this -> setChemin($slug);
        
        //Lecture du contenu si fichier trouvé et type défini
        if($this -> existe() && is_bool($avecContenu))$this -> lire($avecContenu);
    }
    
    /**
     * Lecture du contenu du fichier afin de récupérer les paramètres et le contenu (si demandé)
     * @param Boolean $avecContenu Vrai pour afficher aussi la description. Faux sinon.
     */
    private function lire($avecContenu){
        $finParams = false;
        
        //Ouverture du fichier en mode lecture
        $handle = @fopen($this -> chemin, "r");

        //Si c'est ouvert, on poursuit
        if ($handle) {
            //Lecture du contenu, ligne par ligne afin de pouvoir interrompre si on ne souhaite pas lire le contenu.
            while (($buffer = fgets($handle)) !== false) {
                if(!$finParams){ //Nous nous trouvons dans la partie des paramètres
                    //Conversion de la ligne en tableau, 
                    $ligne = explode(PARAM_SEPARATEUR, $buffer, 2);

                    //Le premier élément est le nom du paramètre alors que le second et dernier est la valeur.
                    if(count($ligne) > 1){
                        $valeurs = explode(PARAM_SEPARATEUR_VALEUR, trim($ligne[1]));

                        $this -> Params[trim($ligne[0])] = count($valeurs) == 1 ? $valeurs[0] : $valeurs;
                    }else $finParams = true; //Nous terminons les paramètres.
                }else if($avecContenu) { //Nous avons terminés avec les paramètres. Ce qui suit correspond au contenu.
                    $this -> Contenu .= $buffer;
                }
            }

            //Fermeture du fichier
            fclose($handle);
        }
    }
    
    /**
     * Permet d'enregistrer le fichier.
     */
    public function enregistrer(){
		//Contenu du fichier qui sera envoyé si le fichier est perdu.
		$fichier_contenu = "";
		
        //Ouverture (+ création si nécessaire)
        $source = fopen($this -> chemin, 'w');
        
        //On passe en revue tous les paramètres
        foreach ($this -> Params as $param => $valeur) {
            //Ecriture dans le fichier
            fwrite($source, $param . PARAM_SEPARATEUR . (is_array($valeur) ? implode(PARAM_SEPARATEUR_VALEUR, $valeur) : $valeur) . "\n");
			
			//Sauvegarde
			$fichier_contenu .= $param . PARAM_SEPARATEUR . (is_array($valeur) ? implode(PARAM_SEPARATEUR_VALEUR, $valeur) : $valeur) . "\n";
        }
        
        //On ajoute maintenant le contenu
        fwrite($source, "\n" . str_replace("\r", "", $this -> Contenu));
        
        //Fermeture du fichier
        fclose($source);
		
		//Il arrive que le fichier disparaisse.. alors on teste l'existence du fichier
		$this -> existe = file_exists($this -> chemin);
		
		//Si le fichier n'existe pas, ce n'est pas normal. On envoie donc une copie par mail.
		if(!$this -> existe)envoyerMail("Fichier perdu ?", "Destination : " . $this -> chemin . "\n\n$fichier_contenu");
    }
    
    /**
     * Permet de tester l'existe du fichier/répertoire sur le serveur ou non.
     * @return Vrai si le fichier existe, faux, sinon.
     */
    public function existe(){
        return $this -> existe;
    }

    /**
     * Permet de retourner le contenu du fichier.
     * @return string Contenu du fichier.
     */
    public function getContenu(){
        return $this -> Contenu;
    }
    
    /**
     * Permet de lire un paramètre du fichier en fournissant le nom, ou le tableau complet.
     * @param String $nom Nom du paramètre à lire
     * @return String Paramètre lu (ou '' vi inexistant)
     */
    public function getParam($nom = ""){
        return $nom == '' ? $this -> Params : (isset($this -> Params[$nom]) ? $this -> Params[$nom] : "");
    }
    
    /**
     * Initialisation du chemin du fichier et recherche de son existance sur le FTP.
     * @param String $chemin Nouveau chemin du fichier
     */
    public function setChemin($chemin){
        $this -> chemin = FOLDER_PAGES . "/" . $chemin . PAGE_EXTENSION;
        $this -> existe = file_exists($this -> chemin);
    }
    
    /**
     * Permet d'ajouter ou modifier un paramètre au fichier
     * @param String $clef Nom du paramètre
     * @param String $valeur Valeur du paramètre
     */
    public function setParam($clef, $valeur){
        $this -> Params[$clef] = $valeur;
    }
    
    /**
     * Permet de modifier le contenu du fichier.
     * @param String $contenu Nouveau contenu du fichier.
     */
    public function setContenu($contenu){
        $this -> Contenu = $contenu;
    }
    
    /**
     * Suppression d'un paramètre
     * @param String $clef Nom du paramètre à supprimer
     */
    public function supprimerParam($clef){
        unset($this -> Params[$clef]);
    }
    
    /**
     * Suppression du fichier en cours.
     */
    public function supprimerFichier(){
        unlink($this -> chemin);
    }
}

?>