<?php

/**
 * Pour une maintenance plus aisée, toutes les éléments de lecture/d'écriture vers le forum sont regroupés ici.
 * La classe Utilisateur utilise aussi certaines choses spécifiques à phpBB.
 */
class Passerelle {
    private static $_instance = null;
	
	private static $utilisateur = null;
	private static $existeForum = false;
	
	private static $db;
	private static $auth;
	private static $cache;
	
    /**
     * Ouverture d'un session
     */
    private function __construct(){
		//Variables globales du forum
		global $user, $auth, $db, $config, $cache, $phpbb_root_path, $phpEx;
		
		//Création d'un utilisateur sans lien avec le forum
		require_once 'classes/utilisateur.php';
		self::$utilisateur = new Utilisateur(); 
		
		//Création d'un pont uniquement si un chemin vers le forum est fourni
		if(FOLDER_FORUM != ""){
			//Nécessaire pour le fonctionnement du forum
			define('IN_PHPBB', true);
			$phpbb_root_path = is_null($phpbb_root_path) ? FOLDER_FORUM ."/" : $phpbb_root_path;
			$phpEx = substr(strrchr(__FILE__, '.'), 1);
			
			include($phpbb_root_path . 'common.' . $phpEx);
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
			include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
			
			//Ouverture du lien avec le forum
			self::$utilisateur -> initialiser($user, $auth);
			$auth -> acl(self::$utilisateur->getUserData());
			self::$existeForum = true;
			
			//On mémorise certaines variables globales
			self::$db = $db;
			self::$auth = $auth;
			self::$cache = $cache;
		}
    }
	
    /**
     * Comme son nom l'indique, sert à recalculer (correctement) les compteurs de message pour les utilisateurs
     */
	public static function recalculerCompteurPosts(){
		self::getInstance();
		
		self::$db->sql_query("
			UPDATE 
				fofo_users U
				INNER JOIN (select poster_id, count(1) compteur from fofo_posts group by poster_id) TMP ON TMP.poster_id = U.user_id
			SET U.user_posts = TMP.compteur
		");
	}
	
    /**
     * Sert à effectuer un changement de propriétaire pour une liste de topics.
	 * @param String $créateur_login login du nouveau propriétaire
	 * @param String $topics Liste de topics séparés par des virgules
     */
	public static function changerPropriétaire($créateur_login, $topics){
		self::getInstance();
		
		// Ouverture d'une transaction
		self::$db->sql_transaction('begin');
		
		//On change le propriétaire du 1er message du topic
		self::$db->sql_query("UPDATE " . POSTS_TABLE . " AS p SET poster_id = u.user_id, poster_ip = u.user_ip, post_username = u.username FROM " . TOPICS_TABLE . " AS t, " . USERS_TABLE . " AS u WHERE p.post_id = t.topic_first_post_id AND t.topic_id IN ($topics) AND u.username_clean = '$créateur_login'");

		//On change le propriétaire du topic
		self::$db->sql_query("UPDATE " . TOPICS_TABLE . " AS t SET topic_poster = u.user_id, topic_first_poster_name = u.username, topic_first_poster_colour = u.user_colour FROM " . USERS_TABLE . " u WHERE t.topic_id IN ($topics) AND u.username_clean = '$créateur_login'");

		//On change les infos du dernier message contenu dans le topic si le dernier message est le 1er message
		self::$db->sql_query("UPDATE " . TOPICS_TABLE . " AS t SET topic_last_poster_id = topic_poster, topic_last_poster_name = topic_first_poster_name, topic_last_poster_colour = topic_first_poster_colour WHERE t.topic_id IN ($topics) AND t.topic_first_post_id = t.topic_last_post_id");

		//On change les infos du dernier message contenu dans le forum si le dernier message est le 1er message du topic
		self::$db->sql_query("UPDATE " . FORUMS_TABLE . " f SET forum_last_poster_id = t.topic_poster, forum_last_poster_name = t.topic_last_poster_name, forum_last_poster_colour = t.topic_last_poster_colour FROM " . TOPICS_TABLE . " t WHERE t.topic_id IN ($topics) AND f.forum_last_post_id = t.topic_first_post_id");

		//On valide la transaction.
		self::$db->sql_transaction('commit');		
	}
	
    /**
     * Création d'un sous-forum.
	 * @param $parent_id ID du forum qui accueillera le nouveau forum
	 * @param $titre Titre du message
	 * @param $contenu Contenu du message
	 * @return Integer ID du forum créé
     */
	public static function créerForum($parent_id, $titre, $contenu, $onIndex = 0){
		self::getInstance();
		
		// Ouverture d'une transaction
		self::$db->sql_transaction('begin');
		
		$message_parser = new parse_message();
		$message_parser->parse_message(utf8_normalize_nfc($contenu));
		$message_parser->parse(true, true, true);
		
		$forum_data = array(
			'parent_id'				=> $parent_id,
			'forum_name'			=> $titre,
			'forum_desc'			=> $message_parser->message,
			'forum_desc_uid'		=> $message_parser->bbcode_uid,
			'forum_desc_bitfield'	=> $message_parser->bbcode_bitfield,
			'forum_type'			=> 1,
			'forum_flags'			=> 0,
			'prune_days'			=> 7,
			'prune_viewed'			=> 7,
			'prune_freq'			=> 1,
			'display_on_index'		=> $onIndex,
			'forum_parents'			=> '',
			'forum_rules'			=> ''
		);
		
		//Lecture des infos du parent
		$sql = 'SELECT left_id, right_id, forum_type FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $parent_id;
		$result = self::$db->sql_query($sql);
		$row = self::$db->sql_fetchrow($result);
		self::$db->sql_freeresult($result);
		
		//On change les positions afin de faire de la place pour notre forum
		self::$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET left_id = left_id + 2, right_id = right_id + 2 WHERE left_id > ' . $row['right_id']);
		self::$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET right_id = right_id + 2 WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id');
		
		//On définit la position du futur forum
		$forum_data['left_id'] = $row['right_id'];
		$forum_data['right_id'] = $row['right_id'] + 1;

		//Création réelle du forum
		self::$db->sql_query('INSERT INTO ' . FORUMS_TABLE . ' ' . self::$db->sql_build_array('INSERT', $forum_data));
		
		//On récupère l'ID du nouveau forum
		$forum_data['forum_id'] = self::$db->sql_nextid();
		
		//On vide le cache SQL
		self::$cache->destroy('sql', FORUMS_TABLE);

		//On duplique les permissions du parent + MAJ du cache
		copy_forum_permissions($parent_id, $forum_data['forum_id'], false);
		cache_moderators();
		
		//On vide le cache des permissions
		self::$auth->acl_clear_prefetch();

		// On valide la transaction.
		self::$db->sql_transaction('commit');
		
		return $forum_data['forum_id'];
	}
	
    /**
     * Création d'un topic, pour un utilisateur choisi.
	 * @param $forum_id ID du forum qui accueillera le message
	 * @param $login_clean Login de l'utilisateur qui sera le propriétaire
	 * @param $titre Titre du message
	 * @param $contenu Contenu du message
	 * @param $current_time Date et heure de publication du message (utiliser time() sinon)
	 * @param $current_time Date et heure de publication du message (utiliser time() sinon)
     * @param integer $type Type de topic à créer : POST_NORMAL, POST_STICKY, POST_ANNOUNCE ou POST_GLOBAL.
	 * @return Integer Renvoie l'ID du topic créé
     */
	public static function créerTopic($forum_id, $login_clean, $titre, $contenu, $current_time, $type){
		self::getInstance();
		
		// Ouverture d'une transaction
		self::$db->sql_transaction('begin');
		
		//A partir du login fourni, on va récupérer ses infos. Sinon, on récupère à partir de l'utilisateur courant (connecté ou non).
		$infos_utilisateur = self::lireUtilisateur($login_clean);
		if($infos_utilisateur){
			$user_id = (int) $infos_utilisateur["user_id"];
			$login = $infos_utilisateur["username"];
			$user_colour = $infos_utilisateur["user_colour"];
		}else{
			$user_id = self::$utilisateur -> getID();
			$login = self::$utilisateur -> getUsername();
			$user_colour = self::$utilisateur -> getUserColor();
		}
		
		//Création du Topic
		$sql_data = array(
			'topic_poster'				=> $user_id,
			'topic_time'				=> $current_time,
			'topic_last_view_time'		=> $current_time,
			'forum_id'					=> $forum_id,
			'topic_approved'			=> 1,
			'topic_title'				=> $titre,
			'topic_first_poster_name'	=> $login,
			'topic_first_poster_colour'	=> $user_colour,
			'topic_type'				=> $type,
			'topic_time_limit'			=> 0,
			'topic_attachment'			=> 0
		);
		self::$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . self::$db->sql_build_array('INSERT', $sql_data));
		
		//Lecture de l'ID du topic créé
		$topic_id = self::$db->sql_nextid();
		
		//MAJ du forum (compteurs)
		self::$db->sql_query("UPDATE " . FORUMS_TABLE . ' SET forum_topics_real = forum_topics_real + 1, forum_topics = forum_topics + 1 WHERE forum_id = ' . $forum_id);
		
		// Mark this topic as read
		// We do not use post_time here, this is intended (post_time can have a date in the past if editing a message)
		markread('topic', 0, $topic_id, $current_time);
		
		//Création du 1er message
		self::créerPost($forum_id, $topic_id, $login_clean, $titre, $contenu, $current_time, true, true);

		// On valide la transaction.
		self::$db->sql_transaction('commit');
		
		return $topic_id;
	}
	
    /**
     * Création d'un message sur le forum, pour un utilisateur choisi.
	 * @param $forum_id ID du forum qui accueillera le message
	 * @param $topic_id ID du topic qui accueillera le message
	 * @param $login_clean Login de l'utilisateur qui sera le propriétaire
	 * @param $titre Titre du message
	 * @param $contenu Contenu du message
	 * @param $current_time Date et heure de publication du message (utiliser time() sinon)
	 * @param $first (faux) Vrai si le message est le 1er du topic. Faux sinon
	 * @param $sousTransac (faux) Vrai si une une transaction est déjà ouverte. Faux sinon
     */
	public static function créerPost($forum_id, $topic_id, $login_clean, $titre, $contenu, $current_time, $first = false, $sousTransac = false){
		self::getInstance();
		
		//On parse le message pour PHPBB
		$message_parser = new parse_message();
		$message_parser->parse_message(utf8_normalize_nfc($contenu));
		$message_parser->parse(true, true, true);
		
		//A partir du login fourni, on va récupérer ses infos. Sinon, on récupère à partir de l'utilisateur courant (connecté ou non).
		$infos_utilisateur = self::lireUtilisateur($login_clean);
		if($infos_utilisateur){
			$user_id = (int) $infos_utilisateur["user_id"];
			$user_ip = $infos_utilisateur["user_ip"];
			$login = $infos_utilisateur["username"];
			$user_colour = $infos_utilisateur["user_colour"];
		}else{
			$user_id = self::$utilisateur -> getID();
			$user_ip = self::$utilisateur -> getIP();
			$login = self::$utilisateur -> getUsername();
			$user_colour = self::$utilisateur -> getUserColor();
		}

		// Ouverture d'une transaction
		if(!$sousTransac)self::$db->sql_transaction('begin');
		
		//Informations du futur post
		$sql_data = array(
			'forum_id'			=> $forum_id,
			'topic_id' 			=> $topic_id,
			'poster_id'			=> $user_id,
			'poster_ip'			=> $user_ip,
			'post_time'			=> $current_time,
			'post_approved'		=> 1,
			'enable_bbcode'		=> 1,
			'enable_smilies'	=> 1,
			'enable_magic_url'	=> 1,
			'enable_sig'		=> 1,
			'post_username'		=> $login,
			'post_subject'		=> $titre,
			'post_text'			=> $message_parser->message,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
			'post_checksum'		=> '',
			'post_attachment'	=> 0,
			'post_postcount'	=> (self::$auth->acl_get('f_postcount', $forum_id)) ? 1 : 0,
			'post_edit_locked'	=> 0
		);

		//Création du post
		self::$db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . self::$db->sql_build_array('INSERT', $sql_data));
		$post_id = self::$db->sql_nextid();
		
		//Informations pour MAJ de l'utilisateur (Dernier message + compteur)
		$sql_data = array();
		$sql_data[USERS_TABLE][] = "user_lastpost_time = $current_time, user_posts = user_posts + 1";

		//Informations pour MAJ du topic (Dernier post créé)
		if($first)$sql_data[TOPICS_TABLE][] = "topic_first_post_id = $post_id";
		$sql_data[TOPICS_TABLE][] = "topic_last_post_id = $post_id";
		$sql_data[TOPICS_TABLE][] = "topic_last_post_time = '$current_time'";
		$sql_data[TOPICS_TABLE][] = "topic_last_poster_id = $user_id";
		$sql_data[TOPICS_TABLE][] = "topic_last_poster_name = '$login'";
		$sql_data[TOPICS_TABLE][] = "topic_last_poster_colour = '$user_colour'";
		$sql_data[TOPICS_TABLE][] = "topic_last_post_subject = '$titre'";
		
		//Informations pour MAJ du forum (Dernier post créé + compteur)
		$sql_data[FORUMS_TABLE][] = 'forum_posts = forum_posts + 1';
		$sql_data[FORUMS_TABLE][] = 'forum_last_post_id = ' . $post_id;
		$sql_data[FORUMS_TABLE][] = "forum_last_post_subject = '" . self::$db->sql_escape($titre) . "'";
		$sql_data[FORUMS_TABLE][] = 'forum_last_post_time = ' . $current_time;
		$sql_data[FORUMS_TABLE][] = 'forum_last_poster_id = ' . $user_id;
		$sql_data[FORUMS_TABLE][] = "forum_last_poster_name = '" . $login . "'";
		$sql_data[FORUMS_TABLE][] = "forum_last_poster_colour = '" . self::$db->sql_escape($user->data['user_colour']) . "'";
		
		//On applique les MAJ suite à la création du Post.
		$where_sql = array(TOPICS_TABLE => 'topic_id = ' . $topic_id, FORUMS_TABLE => 'forum_id = ' . $forum_id, USERS_TABLE => 'user_id = ' . $user_id);
		foreach ($sql_data as $table => $update_ary)
		{
			$sql = "UPDATE $table SET " . implode(', ', $update_ary) . ' WHERE ' . $where_sql[$table];
			self::$db->sql_query($sql);
		}

		// On valide la transaction.
		if(!$sousTransac)self::$db->sql_transaction('commit');
		
		//Le post créé est automatiquement défini en tant que lu pour l'utilisateur
		markread('post', $forum_id, $topic_id, $current_time, $user_id);
	}
	
    /**
     * Génère un slug pour accéder au forum/topic correspondant à la page fournie.
	 * @param Page $page Objet Page de la page en cours.
     * @return Array Tableau contenant le slug et le titre ou une chaine vide.
     */
	public static function générerLienForum($page){
		self::getInstance();
		
		$slug = FOLDER_FORUM;
		$titre = "Critiques / Discussions";
		
		//Inutile de générer un lien s'il n'existe pas de forum
		if(FOLDER_FORUM != ""){
			//On récupère l'id du forum, soit de la page en cours (si existe), soit du parent
			$forum_id = $page -> getForum() >= 0 ? $page -> getForum() : $page -> getForumParent();
			
			//Si pas de forum_id, nous sommes  très probablement à l'accueil du site.
			if($forum_id > -1){
				//On ajoute le forum dans le lien
				$slug .= "/viewforum.php?f=$forum_id";
				
				//S'il y a un forum associé à notre page, inutile d'aller plus loin, on affichera le forum
				if($page -> getForum() < 0 && $page -> getTopic() >= 0){
					//Modification du slug puisque nous allons vers un topic
					$slug = str_replace("viewforum", "viewtopic", $slug) . "&t=" . $page -> getTopic();
					
					//On adapte aussi le titre pour y ajouter le total de messages (sans prendre en compte le 1er)
					$totalPosts = self::getTotalPosts($page -> getTopic()) -1;
					if($totalPosts > 0)$titre .= " ($totalPosts)";
				}
			}
		}
		
		return $slug != "" ? array($slug, $titre) : "";
	}
	
    /**
     * A partir d'un login, on va générer un lien vers le profil correspondant
     * @return String Lien vers un profil ou le login fourni s'il n'existe pas en base
     */
	public static function générerLienProfil($login_clean){
		self::getInstance();
		
		//Lecture de l'ID et du login d'un utilisateur
		$row = self::lireUtilisateur($login_clean);
		
		//On change le login par un lien vers le profil si la requête renvoie quelque chose
		if ($row)$login_clean = "<a href=\"/" . FOLDER_FORUM . "/memberlist.php?mode=viewprofile&u=" . $row['user_id'] . "\" title=\"" . $row['username'] . "\">" . $row['username'] . "</a>";
		
		return $login_clean;
	}
	
    /**
     * Lecture du nombre de messages pour un sujet de discussion donné.
     * @return Integer Total de messages.
     */
	private static function getTotalPosts($topic){
		$sql = "SELECT COUNT(1) AS total FROM " . POSTS_TABLE . " WHERE topic_id = $topic";
		$result = self::$db->sql_query($sql);
		$row = self::$db->sql_fetchrow($result);
		self::$db->sql_freeresult($result);	
		
		return $row ? intval($row["total"]) : 0;
	}
	
    /**
     * Lecture des informations d'un utilisateur
     * @return Array Tableau associatif contenant les informations demandées.
     */
	public static function lireUtilisateur($login_clean){
		self::getInstance();
		
		$sql = 'SELECT user_id, username, user_ip, user_colour FROM ' . USERS_TABLE . " WHERE username_clean = '$login_clean'";
		$result = self::$db->sql_query($sql);
		$row = self::$db->sql_fetchrow($result);
		self::$db->sql_freeresult($result);
		
		return $row;
	}
	
    /**
     * Accès à l'objet Utilisateur
     * @return Utilisateur
     */
	public static function getUtilisateur(){
		self::getInstance();
		
		return self::$utilisateur;
	}
	
    /**
     * Le forum existe ? Si oui le pont entre le site et le forum est censé être établi.
     * @return Boolean Vrai si le forum existe. Faux, sinon.
     */
	public static function ExisteForum(){
		self::getInstance();
		
		return self::$existeForum;		
	}
	
    /**
     * Méthode principale qui sert à créer un objet Session s'il n'en existe aucune
     * @return Session Nouvelle instance de la classe en cours.
     */
    public static function getInstance() {
        //Création d'une nouvelle instance s'il n'en existe aucune.
        if(is_null(self::$_instance))self::$_instance = new Passerelle();
        
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
