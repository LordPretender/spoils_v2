<?php

require_once 'includes/params.php';
require_once 'includes/functions_php.php';
require_once 'classes/ftp.php';
require_once 'classes/fichier.php';
require_once 'classes/page.php';
require_once 'classes/rss.php';
require_once 'classes/contributeurs.php';
require_once 'classes/passerelle.php';

$limite = 100; //On ne garde que les 100 derniers fichiers modifiés.
$limite_recents = 5; //Pour le bloc HTML des dernières modifications.

$ftp = new FTP();

//Lecture des contributeurs
$contributeurs = new Contributeurs();

//Ouverture (+ création si nécessaire) des fichiers pour le bloc HTML, le sitemap, le rss et les stats
$sd_recents = fopen(FOLDER_DATAS . "/modifications.txt", 'w');
$sd_sitemap = fopen("sitemap.xml", 'w');
$flux = new RSS();
$sd_stats = fopen(FOLDER_DATAS . "/statistiques.txt", 'w');

$contenus_exclusions = array("", "404", "faq", "0", "a-propos");

//Entête du sitemap
fwrite($sd_sitemap, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");

//Création du fichier de contenus
$contenus = new Fichier("../contenus", "");
$contenus ->setParam("titre", "Résultats de la recherche");

//Initialisation des variables qui serviront pour les stats
$totalCatégories = 0;
$totalPages = 0;
$totalContributions = 0;

//Contiendra tous les contributeurs avec les topics à modifier
$toChangeProprio = array();

//Lecture des X derniers fichiers modifiés récemment puis ajout dans le fichier.
foreach ($ftp -> getTousLesFichiers() as $slug => $date) {
    //On ignore les slugs qui sont exclus
    if(!in_array($slug, $contenus_exclusions)){
        //Lecture du fichier
        $page = new Page($slug);
        
        //Calcul du nombre de pages et du nombre de catégories
        if($page -> estCatégorie()){
            $totalCatégories++;
        }else $totalPages++;
        
        //Calcul du nombre de contributions (création/modification -> une page peut avoir eu plusieurs contributions)
		$tabContributeurs = $page -> getArrayContributeurs();
        $totalContributions += count($tabContributeurs);
        
        //Contenu brut sur une seule ligne
        $contenu = preg_replace("/[ ]+/i", " ", str_replace("\n", " ", $page -> getContenu(false)));

        //On ne prend que les X 1er pour le bloc HTML des dernières modifications
        if($limite_recents > 0){
            $limite_recents--;
            fwrite($sd_recents, $page -> générerLien() . PARAM_SEPARATEUR_VALEUR . $page -> générerTitreComplet() . PARAM_SEPARATEUR . résumer(180, $contenu) . "\n");
        }

        if($limite > 0){
            $limite--;

            //Timestamp de la date
            $timestampDate = strtotime($date);

            //On remplit le flux RSS
            $flux -> ajouterItem($page -> générerTitreComplet(), $page -> générerLien(), résumer(TAILLE_RESUME, $contenu), date("r", $timestampDate));

            //On remplit le sitemap
            fwrite($sd_sitemap, "\t<url>\n\t\t<loc>" . SITE_URL . $page -> générerLien() . "</loc>\n\t\t<lastmod>" . date("Y-m-d", $timestampDate) . "</lastmod>\n\t\t<changefreq>weekly</changefreq>\n\t\t<priority>0.5</priority>\n\t</url>\n");
        }

        //On ajoute un nouveau paramètre pour ajouter le contenu du fichier en cours
        $contenus ->setParam($page -> générerLien() . PARAM_SEPARATEUR_VALEUR . $page -> générerTitreComplet(), $contenu);
		
		//On récupère le créateur
		$créateur_login = $contributeurs -> getLogin($tabContributeurs[0]);
		
		//Les contributeurs qui ne sont pas encore inscrits... on doit mémoriser les topics afin de les synchroniser le jour où ils s'inscrivent
		if(!$contributeurs -> estLié($créateur_login)){
			//On récupère le topic
			$topic = $page -> getTopic();
			
			//Qu'on ajoute ensuite à la liste
			if($topic >= 0)$toChangeProprio[$créateur_login][] = $topic;
		}
    }
}

//Enregistrement des stats
fwrite($sd_stats, "Date mise à jour" . PARAM_SEPARATEUR . date ("d/m/Y H:i") . "\n");
fwrite($sd_stats, "Total de catégories" . PARAM_SEPARATEUR . $totalCatégories . "\n");
fwrite($sd_stats, "Total de pages" . PARAM_SEPARATEUR . $totalPages . "\n");
fwrite($sd_stats, "Total de contributions" . PARAM_SEPARATEUR . $totalContributions . "\n");
fwrite($sd_stats, "Total de " . PARAM_SEPARATEUR . $contributeurs -> Total() . "\n");

//Pied du sitemap
fwrite($sd_sitemap, "</urlset>");

//On ajoute une petite description au fichier des contenus, puis on enregistre.
$contenus -> setContenu("Retrouvez, ci-dessous, les résultats de votre recherche, les pages récemment modifiées en premier.");
$contenus -> enregistrer();

//On passe en revue les contributeurs pas encore liés au forum (car pas inscrit).
foreach($toChangeProprio as $createur => $tabTopics){
	//On lit les infos de l'utilisateur juste pour savoir si le login existe
	//Si oui, on peut synchroniser et définir le login en tant que lié
	$infosUtilisateur = Passerelle::lireUtilisateur($createur);
	if($infosUtilisateur){
		//Changement de propriétaire des topics
		Passerelle::changerPropriétaire($createur, implode(', ', $tabTopics));
		
		//On définit le contributeur en tant que lié
		$contributeurs->estLié($createur, 1);
	}
}

//On applique les changements au niveau des Contributeurs
$contributeurs -> enregistrer();

//Mise à jour du compteur de message des utilisateurs
Passerelle::recalculerCompteurPosts();

//Fermeture des fichiers
fclose($sd_recents);
fclose($sd_sitemap);
fclose($sd_stats);

//Enregistrement du flux
$flux -> enregistrer();

?>
