<?php

class AutreTemplate extends SiteTemplate {
    private $msg_type = "";
    private $msg_text = "";
    private $mail_type;
    
    public function __construct($slug){
        parent::__construct("");
        
        //Définition du template pour le contenu
        $this -> gabarit = $slug;
        
        //Conversion en page spéciale
        $this -> page ->estPageSpéciale($slug, PAGE_CONTACT_TITRE, "Besoin de contacter l'administrateur et son équipe ? Cette page vous permet d'y remédier et de tenter de les joindre.");
        
        //Types de mail
        $this -> mail_type = array('Question', 'Bug', 'Suggestion', 'Autre');
    }
    
    /**
     * Permet de contrôler le contenu du formulaire.
     * @return Boolean Vrai si le formulaire est conforme. Faux, sinon.
     */
    private function contrôler($name, $subject, $contenu, $email){
        $ok = true;
        
        //Champ anti-spam
        $spam_check = isset($_POST['spam_check']) ? $_POST['spam_check'] : '';

        if($name == '' || $subject == '' || $contenu == '' || $email == ''){
            $this -> msg_type = "error";
            $this -> msg_text = "Les champs marqués d'une étoile (*) sont requis.";
            
            $ok = false;
        }elseif (filter_var($email, FILTER_VALIDATE_EMAIL) !== $email){
            $this -> msg_type = "error";
            $this -> msg_text = "L'adresse mail saisi est incorrect.";
            
            $ok = false;
        }elseif($spam_check != ""){
            $this -> msg_type = "error";
            $this -> msg_text = "Le dernier champ doit être laissé vide.";
            
            $ok = false;
        }
        
        return $ok;
    }
    
    protected function avantChargement(){
        //Formulaire validé ?
        if($_POST){
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
            $contenu = isset($_POST['message']) ? $_POST['message'] : '';
            
            //On teste la conformité
            if($this ->contrôler($name, $subject, $contenu, $email)){
                $type = isset($_POST['type']) ? strtoupper($_POST['type']) : '';
                
                if($this -> envoyer_mail($name, $email, $contenu, $subject, $type)){
                    $this -> msg_type = "success";
                    $this -> msg_text = "Votre message a bien été envoyé. Merci.";
                }else{
                    $this -> msg_type = "error";
                    $this -> msg_text = "Erreur lors de l'envoi de votre message.";
                }
            }
        }
        
        if($this -> msg_type != ""){
            $this -> tpl -> assign( "msg_type", $this -> msg_type );
            $this -> tpl -> assign( "msg_text", $this -> msg_text );                
        }
        $this -> tpl -> assign( "mail_type", $this -> mail_type );                
    }
    
    private function envoyer_mail($name, $email, $contenu, $subject, $type){
	return envoyerMail("[$type] $subject", "Nom : $name\nEmail : $email\n\n$contenu");
    }
}

?>
