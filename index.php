<?php

//Paramètres passés via l'URL.
$slug = isset($_GET['id']) ? ($_GET['id']) : "";

//Redirections permanentes de liens spécifiques
$redirections = array();
$redirections["bleach-le-manga"] = "/mangas/bleach/";
$redirections["bleach-le-manga/bleach-chapitre-480"] = "/mangas/bleach/480/";
$redirections["bleach-le-manga/bleach-chapitre-483"] = "/mangas/bleach/483/";
$redirections["bleach-le-manga/bleach-chapitre-485"] = "/mangas/bleach/485/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-214"] = "/animes/naruto/shippuuden/214/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-219"] = "/animes/naruto/shippuuden/219/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-231"] = "/animes/naruto/shippuuden/231/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-232"] = "/animes/naruto/shippuuden/232/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-235"] = "/animes/naruto/shippuuden/235/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-251"] = "/animes/naruto/shippuuden/251/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-254"] = "/animes/naruto/shippuuden/254/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-256"] = "/animes/naruto/shippuuden/256/";
$redirections["les-episodes-de-naruto-shippuuden/naruto-shippuuden-episode-265"] = "/animes/naruto/shippuuden/265/";
$redirections["les-episodes-de-one-piece/one-piece-episode-520"] = "/animes/one-piece/520/";
$redirections["misfits/misfits-saison-1"] = "/series-tv/misfits/saison-1/";
$redirections["naruto-le-manga/naruto-chapitre-569"] = "/mangas/naruto/569/";
$redirections["naruto/les-episodes-de-naruto-shippuuden"] = "/animes/naruto/shippuuden/";
$redirections["one-piece-le-manga/one-piece-chapitre-631"] = "/mangas/one-piece/631/";
$redirections["one-piece-le-manga/one-piece-chapitre-632"] = "/mangas/one-piece/632/";
$redirections["one-piece/one-piece-le-manga"] = "/mangas/one-piece/";
$redirections["weeds/weeds-saison-1"] = "/series-tv/weeds/saison-1/";
$redirections["weeds-saison-1/weeds-saison-1-episode-4"] = "/series-tv/weeds/saison-1/4/";
$redirections["weeds-saison-1/weeds-saison-1-episode-9"] = "/series-tv/weeds/saison-1/9/";
$redirections["weeds-saison-2/weeds-saison-2-episode-7"] = "/series-tv/weeds/saison-2/7/";
if(array_key_exists($slug, $redirections)){
    header('HTTP/1.1 301 Moved Permanently', false, 301);
    header('Location: ' . $redirections[$slug]);
    exit();
}

//Pages qui n'existent plus
$redirections_deleted = array();
$redirections_deleted[] = "collaboration-new/comics";
$redirections_deleted[] = "collaboration-edit/misfits-saison-1-episode-2";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-225";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-254";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-262";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-262/index.html";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-266";
$redirections_deleted[] = "collaboration-edit/naruto-shippuuden-episode-268";
$redirections_deleted[] = "collaboration-edit/one-piece-episode-527";
$redirections_deleted[] = "collaboration-edit/weeds-saison-2-episode-4";
$redirections_deleted[] = "personnalites/start";
$redirections_deleted[] = "personnalites/start/index.html";
$redirections_deleted[] = "forum/viewforum.php?f=0";
$redirections_deleted[] = "mangas-animes/series/naruto/episodes/naruto-shippuuden/episode..";
if(in_array($slug, $redirections_deleted)){
    header("Status: 410 Gone", false, 410);
    exit();
}

require_once 'includes/params.php';
require_once 'classes/session.php';
require_once 'classes/passerelle.php';
require_once 'classes/ftp.php';
require_once 'classes/demandes.php';
require_once 'classes/fichier.php';
require_once 'classes/fichiertmp.php';
require_once 'classes/page.php';
require_once 'classes/template.php';
require_once 'classes/contributeurs.php';
require_once 'includes/functions_php.php';

//Permet de choisir le bon template en fonction du slug.
$include_file = "pages/$slug.php";
if (file_exists($include_file)){
    require_once $include_file;
    
    $site = new AutreTemplate($slug);
}else{
    $site = new SiteTemplate($slug);
    $site ->ajouterLiensCollaboration();
}

//Liens de navigation
$site -> LienNavigation('FAQ', 'faq');
$site -> LienNavigation('A propos', 'a-propos');
$site -> LienNavigation(PAGE_CONTACT_TITRE, "nous-contacter");

$site -> LienNavigation(Passerelle::générerLienForum($site -> getPage()));


//Liste des partenaires
$site -> LienPartenaire('Manga-News', 'http://www.manga-news.com/');
$site -> LienPartenaire('Annuaire des Séries TV', 'http://www.annuaire-des-series-tele.fr/');
$site -> LienPartenaire('Annuaire série télé', 'http://www.fresh-annuaire.com/serie-tele.html');
$site -> LienPartenaire('Net-liens', 'http://www.net-liens.com');
$site -> LienPartenaire("Gagner de l'argent", 'http://www.netbusiness-team.com/partner_in.php?id=326');

//Afficher la page
$site -> Charger();

?>