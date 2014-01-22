<?php

/**
 * Sert à appeler une méthode (sans paramètres !!) de la classe Fichier.
 * @param Fichier $fichier Instance de l'objet Fichier
 * @param String $method Nom de la méthode à appeler
 */
function methodFichier($fichier, $method){
    return $fichier -> $method();
}

/**
 * Permet d'envoyer un mail à l'administrateur.
 * @param String $sujet Sujet du mail
 * @param String $name Nom de la personne responsable de l'envoi de ce mail
 * @param String $email Adresse mail de la personne qui envoie ce mail
 * @param String $contenu Le contenu du mail
 * @param String $destinataire Adresse mail de la personne qui recevra ce mail
 * @return Boolean Vrai si bien envoyé. Faux, sinon.
 */
function envoyerMail($sujet, $contenu, $destinataire = WEBMASTER_MAIL){
    //Suppression des anti-slashes
    $sujet = stripslashes($sujet);
    $contenu = stripslashes($contenu);
    
    //Ajout de la mention : message automatique
    $contenu .= "\n\n-------------------------------------\nCeci est un message automatique, merci de ne pas y répondre.";

    //Ajout de l'adresse IP de l'utilisateur
    if($destinataire == WEBMASTER_MAIL)$contenu .= "\nIP : " . $_SERVER["REMOTE_ADDR"];

    // On filtre les serveurs qui rencontrent des bogues.
    if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $destinataire)) {
        $passage_ligne = "\r\n";
    }else{
        $passage_ligne = "\n";
    }

    $subject = "[" . SITE_NOM . "] $sujet";

    //=====Création de la boundary
    $boundary = "-----=".md5(rand());
    //==========

    //=====Création du header de l'e-mail.
    $header = "From: \"" . SITE_NOM . "\"<" . SITE_MAIL . ">".$passage_ligne;
    $header.= "Reply-to: \"" . SITE_NOM . "\"<" . SITE_MAIL . ">".$passage_ligne;
    $header.= "MIME-Version: 1.0".$passage_ligne;
    $header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
    //==========

    //=====Création du message.
    $message = $passage_ligne."--".$boundary.$passage_ligne;
    //=====Ajout du message au format texte.
    $message.= "Content-Type: text/plain; charset=\"UTF-8\"".$passage_ligne;
    $message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
    $message.= $passage_ligne.$contenu.$passage_ligne;
    //==========
    $message.= $passage_ligne."--".$boundary."--".$passage_ligne;
    $message.= $passage_ligne."--".$boundary."--".$passage_ligne;
    //==========

    //=====Envoi de l'e-mail.
    return mail($destinataire,$subject,$message,$header);
}
/**
 * Permet de supprimer les accentuations de la chaîne fournie.
 * @param String $string chaîne dont il faut supprimer les accents
 * @return String chaîne nettoyée.
 */
function supprimerAccents($string){
    //return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    $string = htmlentities($string, ENT_NOQUOTES, 'utf-8');
    $string = preg_replace('#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#', '\1', $string);
    $string = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $string);
    $string = preg_replace('#\&[^;]+\;#', '', $string);
    
    return $string;
}

/**
 * Utilisé pour trier un tableau de Page sur le titre
 * @param Page $fichier1
 * @param Page $fichier2
 * @return Résultat de la comparaison entre les deux fichiers fournies.
 */
function trier ($page1, $page2){
	return strcmp($page1 -> générerTitre(), $page2 -> générerTitre());
}

/**
 * Permet de résumer du texte.
 * @param int $max taille du résumé.
 * @param string $texte texte à résumer
 * @return string texte résumé.
 */
function résumer($max, $texte){
    //Taille du contenu
    $taille = strlen($texte);

    //Génération de l'extrait
    $extrait = mb_substr($texte, 0, $max, 'UTF-8');
    if($taille > $max) {
        $extrait .= "…";
    }
    
    return $extrait;
}

/**
 * 
 * @param Integer $nombrePagination Nombre de pagination.
 * @param Integer $fichierNum Numéro de pagination demandée
 * @param Fichier $fichier Instance du fichier en cours.
 */
function paginer($nombrePagination, $fichierNum, $fichier) {
	if ($fichierNum < 1 && $fichierNum > $nombrePagination) $fichierNum = 1;
	$pagingHtml = '';
	
	if ($nombrePagination > 1) {
		$pagingHtml .= '<ul class="pagination">';
		
		//Ajout du lien pour aller au début ainsi qu'au fichier précédente.
		if ($fichierNum > 1) {
			//$pagingHtml .= ' <span class="first active"><a href="'.$fichier -> générerLien().'" title="Aller à la première fichier">|<<</a></span>';
			$pagingHtml .= ' <li class="pag-prev"><a href="'.$fichier -> générerLien($fichierNum-1).'">&laquo;</a></li>';
		} else {
			//$pagingHtml .= '<span class="first inactive">|<<</span>';
			$pagingHtml .= ' <li class="pag-prev"><a href="#"><U>&laquo;</U></a></li>';
		}
		
		//On n'affiche pas tous les fichiers, juste quelques numéros avant notre numéro et quelques numéros après notre numéro
	 	$min = $fichierNum - PAGINATION_NB;
		$max = $fichierNum + PAGINATION_NB;
		if ($min < 1) {
			$min = 1;
			$max = min($nombrePagination, PAGINATION_NB * 2);
		} else if ($max > $nombrePagination) {
			$max = $nombrePagination;
			$min = max(1, $max - (PAGINATION_NB * 2));
		}
		
		//On ajoute "..." si l'on n'affiche pas toutes les fichiers.
		if ($min > 1) $pagingHtml .= ' <li class="pag-number">...</li>';
		
		//Génération des liens pour les numéros qui seront affichés.
		for ($i=$min; $i<=$max; $i++) {
			if ($i == $fichierNum) {
				$pagingHtml .= ' <li class="pag-number"><a href="#"><U>'.($i).'</U></a></li>';
			} else {
				$t = "Aller à la page ";
				$pagingHtml .= ' <li class="pag-number"><a href="'.$fichier -> générerLien($i).'">'.($i).'</a></li>';
			}
		}
		
		//On ajoute "..." si l'on n'affiche pas toutes les fichiers.
		if ($max < $nombrePagination) $pagingHtml .= ' <li class="pag-number">...</li>';
		
		//Ajout du lien pour aller à la fin ainsi qu'à la fichier suivante.
		if ($fichierNum < $nombrePagination) {
			$pagingHtml .= ' <li class="pag-next"><a href="'.$fichier -> générerLien($fichierNum+1).'">&raquo;</a></li>';
			//$pagingHtml .= ' <span class="last active"><a href="'.$fichier -> générerLien($nombrePagination).'" title="Aller à la dernière page">>>|</a></span>';
		} else {
			$pagingHtml .= ' <li class="pag-next"><a href="#"><U>&raquo;</U></a></li>';
			//$pagingHtml .= ' <span class="last inactive">>>|</span>';
		}
		$pagingHtml .= ' </ul>';
	}
	return $pagingHtml;
}

?>