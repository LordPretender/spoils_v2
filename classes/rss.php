<?php

class RSS{
    protected $doc = "";
    protected $rss = "";
    protected $channel = "";

    /**
     * Création d'une nouvelle instance de RSS qui va permettre de créer un fichier rss.xml
     */
    public function __construct(){
        //Nouveau document XML.
        $this->doc = new DOMDocument("1.0", "UTF-8");
        $this->doc->formatOutput = true;

        //Nouveau flux RSS avec la version voulue.
        $this->rss = $this->doc->createElement("rss");
        $this->rss->setAttribute("version", "2.0");
        $this->doc->appendChild($this->rss);

        //Création du channel
        $this->channel = $this->doc->createElement("channel");
        $this->rss->appendChild($this->channel);

        //Date actuelle au bon format
        $timestampDate = strtotime(date("Y-m-d H:i:s"));
        $date = date("r", $timestampDate);

        //On y ajoute dans ce channel les éléments nécessaires.
        $this->channel->appendChild($this->creerBalise("title", SITE_NOM));
        $this->channel->appendChild($this->creerBalise("link", SITE_URL));
        $this->channel->appendChild($this->creerBalise("description", SITE_SLOGAN));
        $this->channel->appendChild($this->creerBalise("pubDate", $date));
    }

    /**
     * Permet de créer une nouvelle balise avec sa valeur
     * @param String $balise Nom de la balise
     * @param String $str Contenu de la balise
     * @return type+ Noeud créé.
     */
    private function creerBalise($balise, $str){
        $tag = $this->doc->createElement($balise);
        $data = $this->doc->createTextNode($str);

        $tag->appendChild($data);

        return $tag;
    }

    /**
     * Ajoute un nouvel élément à notre flux
     * @param String $title Titre de l'élément
     * @param String $link Lien vers l'élément
     * @param String $desc Résumé de l'élément
     * @param String $date Date de dernière modification
     */
    public function ajouterItem($title, $link, $desc, $date){
        //Création de l'item.
        $item = $this->doc->createElement("item");

        //On y ajoute les éléments nécessaires.
        $item->appendChild($this->creerBalise("title", $title));	
        $item->appendChild($this->creerBalise("link", $link));	
        $item->appendChild($this->creerBalise("description", $desc));	
        $item->appendChild($this->creerBalise("pubDate", $date));	

        //On ajoute cet item au reste du flux.
        $this->channel->appendChild($item);
    }

    /**
     * Enregistrement du flux RSS.
     */
    public function enregistrer(){
        $this->doc->save("rss.xml");
    }

    /**
     * Retourne le contenu du flux
     * @return type Le flux RSS.
     */
    public function afficher(){
        return $this->doc->saveXML();
    }

}

?>
