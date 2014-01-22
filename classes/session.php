<?php

final class SiteSession{
    private static $_instance = null;

    /**
     * Ouverture d'un session
     */
    private function __construct(){
        ini_set("session.use_trans_sid","0"); 
        ini_set("url_rewriter.tags","");

        session_start();
    }

    /**
     * Teste l'existence d'une variable.
     * @param String $nom Nom de la variable à tester
     * @return Boolean Vrai si la variable existe. Faux, sinon.
     */
    public static function isExist($nom){
        self::getInstance();

        return isset($_SESSION[$nom]) ? true : false;
    }

    /**
     * Suppression d'une variable
     * @param String $nom Nom de la variable à supprimer
     */
    public static function supprime($nom){
        self::getInstance();

        unset($_SESSION[$nom]);
    }

    /**
     * Lecture de la valeur d'une variable
     * @param String $nom Nom de la variable
     * @return Mixed Valeur de la variable
     */
    public static function getVariable($nom){
        self::getInstance();

        return $_SESSION[$nom];
    }

    /**
     * Création ou modification d'une variable
     * @param String $nom Nom de la variable
     * @param Mixed $value Valeur de la variable
     */
    public static function setVariable($nom, $value){
        self::getInstance();

        $_SESSION[$nom] = $value;
    }

    /**
     * Méthode principale qui sert à créer un objet Session s'il n'en existe aucune
     * @return Session Nouvelle instance de la classe en cours.
     */
    public static function getInstance() {
        //Création d'une nouvelle instance s'il n'en existe aucune.
        if(is_null(self::$_instance))self::$_instance = new SiteSession();
        
        //Qu'on renvoit ensuite
        return self::$_instance;
    }

    /**
     * Sert à se prémunir contre le clonage de notre objet.
     */
    public function __clone(){
        trigger_error('Le clônage n\'est pas autorisé.', E_USER_ERROR);
    }

    public function __toString(){
        return "OK.";
    }
}

?>