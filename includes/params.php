<?php

//Url du site réel
$site_reel = "www.spoils.fr";
define("SITE_REEL", "http://" . $site_reel);

//Si la page appelée est exécutée via ligne de commande (via une CRON par exemple), il faut définir manuellement le domaine du serveur.
if (!isset($_SERVER["HTTP_HOST"]))$_SERVER["HTTP_HOST"] = $site_reel;

//Paramètres site
define("SITE_NOM", "Spoils");
define("SITE_SLOGAN", "Plus qu'un résumé, l'histoire... du Spoil !");
define("SITE_URL", "http://" . $_SERVER["HTTP_HOST"]);
define("SITE_KEYS", "spoil, spoiler");
define("SITE_MAIL", "postmaster@spoils.fr");

//Paramètres dossiers systèmes
define("FOLDER_DATAS", "datas");
define("FOLDER_PAGES", FOLDER_DATAS . "/pages");
define("FOLDER_HTML", SITE_URL . "/html");
define("FOLDER_FORUM", "forum");

//Paramètres fichiers
define("FOLDER_INDEX", "index");
define("PAGE_EXTENSION", ".txt");
define("PARAM_SEPARATEUR", " : ");
define("PARAM_SEPARATEUR_VALEUR", "#");

//Paramètres divers
define("TAILLE_RESUME", 300); //Taille des résumés
define("PAGINATION", 10); //Nombre d'éléments dans une page
define("PAGINATION_NB", 5); //Nombre de liens à afficher avant/après le numéro de page en cours.
define("WEBMASTER_MAIL", 'lordp.webmaster@gmail.com');

//Pages spéciales
define("PAGE_CONTACT_TITRE","Nous Contacter");
define("PAGE_PAGE_CREER_TITRE","Nouvelle page");
define("PAGE_CAT_CREER_TITRE","Nouvelle catégorie");
define("PAGE_PAGE_MODIFIER_TITRE","Modifier la page");
define("PAGE_DEMANDES","Consulter les demandes");

//Types de contenu des pages
$types = array('Série', 'Saison', 'Chapitre', 'Épisode', '[Volume]'); //Liste des différents types de contenu (peut aussi ne pas avoir de type : -1).
$types_catégories = array(0, 1); //Liste des types qui ne concernent que des catégories
$types_masquer = array(0); //Liste des types dont nous ne souhaitons pas afficher le type

//Liste des utilisateurs de php autorisés à approuver des Demandes
$approbateurs = array('lordpretender');

?>
