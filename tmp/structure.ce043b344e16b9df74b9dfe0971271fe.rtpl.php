<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="fr"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="fr"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="fr"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="fr"> <!--<![endif]-->
<head>
    <!--  Basic Meta and Title -->
    <meta charset="utf-8">
    <title><?php echo $meta_title;?><?php if( $pagination > 1 ){ ?> - Page <?php echo $pagination;?><?php } ?></title>
    <meta name="description" content="<?php if( $pagination > 1 ){ ?>Page <?php echo $pagination;?>... <?php } ?><?php echo $meta_description;?>" />
    <meta name="author" content="LordPretender">
    <meta name="keywords" content="<?php echo $site_keywords;?>" />
	
    <!-- Mobile Device Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo $site_tpl;?>css/formalize.css">
    <link rel="stylesheet" href="<?php echo $site_tpl;?>css/style.css">
    <link rel="stylesheet" href="<?php echo $site_tpl;?>css/layout.css">
    <link rel="stylesheet" href="<?php echo $site_tpl;?>css/superfish.css">
    <link rel="stylesheet" href="<?php echo $site_tpl;?>css/devices.css">

    <!--[if lt IE 9]>
        <link rel="stylesheet" href="<?php echo $site_tpl;?>css/ie.css">
    <![endif]-->

    <link rel="stylesheet" type="text/css" href="<?php echo $site_tpl;?>css/theme_red.css" title="theme_red" media="screen" />

    <!--[if lt IE 9]>
        <script src="<?php echo $site_tpl;?>js/html5.js"></script>
    <![endif]-->

    <!-- Favicons -->
    <link rel="shortcut icon" href="<?php echo $site_tpl;?>images/favicons/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo $site_tpl;?>images/favicons/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $site_tpl;?>images/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $site_tpl;?>images/favicons/apple-touch-icon-114x114.png">

    <link rel="alternate" type="application/rss+xml" href="http://feeds.feedburner.com/spoils/RqLB" title="Les dernières modifications.">

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script src="<?php echo $site_tpl;?>js/jquery.textPlaceholder.js"></script>
    <script type="text/javascript">
        $(function(){
            $(".text-field").textPlaceholder();
            $(".text-area").textPlaceholder();
            $(".search-field").textPlaceholder();
        });
    </script>

    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-32301432-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
    
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
</head>
<body>
    <!-- Page Header START -->
    <header>
        <div class="container">
            <div class="two columns">
                <a href="/"><img src="<?php echo $site_tpl;?>images/main/logo.png" alt="" /></a>
            </div>

            <!-- Navigation START -->
            <div class="ten columns">
                <nav>
                    <ul class="sf-menu">
                        <li><a<?php if( $fichier_slug == '' || ($fichier_racine == '' && !$fichier_type) ){ ?> class="active-tab"<?php } ?> href="/" title="Accueil">Accueil</a></li>
                        <?php $counter1=-1; if( isset($themes) && is_array($themes) && sizeof($themes) ) foreach( $themes as $key1 => $value1 ){ $counter1++; ?>

                        <li><a<?php if( $fichier_racine == $key1 || $fichier_slug == $key1 ){ ?> class="active-tab"<?php } ?> href="/<?php echo $key1;?>/" title="<?php echo $value1;?>"><?php echo $value1;?></a></li>
                        <?php } ?>

                    </ul>
                </nav>
            </div>
            <!-- Navigation END -->
        </div>
    </header>
    <div id="header-bottom"></div>
    <!-- Page Header END -->


    <section class="container">
        
        <!-- Main Content START -->
        <div id="main">
            <div id="pub_728">
                <script type="text/javascript"><!--
                    google_ad_client = "ca-pub-5126470956370561";
                    google_ad_slot = "4372856667";
                    google_ad_width = 728;
                    google_ad_height = 90;
                    //-->
                </script>
                <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
            </div>
            
            <div class="eight columns">
                <h1 class="page-title"><?php echo $fichier_titre;?></h1>

                <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("".$contenu."") . ( substr("".$contenu."",-1,1) != "/" ? "/" : "" ) . basename("".$contenu."") );?>

            </div>
        </div>
        <!-- Main Content END -->

        <!-- Sidebar Content START -->
        <div id="sidebar" class="four columns">
            <aside>
                
                <div class="search-widget">
                    <form class="search-form" id="search-form" action="/recherche/" method="post">
                        <fieldset>
                            <input type="text" class="search-field" value="" id="keyword" name="keyword" placeholder="Recherche..." />
                            <input type="submit" id="search-submit" value="Lancer">
                        </fieldset>
                    </form>
                </div>

                <div class="menu-widget">
                    <h5>Navigation<?php if( $approbateur ){ ?> (+)<?php } ?></h5>

                    <ul>
						<li class="odd"><a href="#" onclick="window.open('http://participation.easy-hebergement.fr/1d3cc52372661e6c3d33cf94947a818b8a12db3f','','toolbar=0, location=0, directories=0, status=1, scrollbars=0, resizable=0, copyhistory=0, menuBar=0, width=520, height=730');return(false)">Faire un Don</a></li>
                    <?php $counter1=-1; if( isset($navigation) && is_array($navigation) && sizeof($navigation) ) foreach( $navigation as $key1 => $value1 ){ $counter1++; ?>

                        <li <?php if( $key1 & 1 ){ ?>class="odd"<?php }else{ ?>class="even"<?php } ?>><a href="<?php echo $value1["slug"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></li>
                    <?php } ?>

                    </ul>
                </div>
				
				<?php if( $module_approbateur ){ ?>

                <div class="menu-widget">
                    <h5>Approbateur</h5>

                    <ul>
                    <?php $counter1=-1; if( isset($module_approbateur) && is_array($module_approbateur) && sizeof($module_approbateur) ) foreach( $module_approbateur as $key1 => $value1 ){ $counter1++; ?>

                        <li <?php if( $key1 & 1 ){ ?>class="odd"<?php }else{ ?>class="even"<?php } ?>><a href="<?php echo $value1["slug"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></li>
                    <?php } ?>

                    </ul>
                </div>
				<?php } ?>

                <div id="bloc_300" class="text-widget">
                    <div id="pub_300">
                        <script type="text/javascript"><!--
                                google_ad_client = "ca-pub-5126470956370561";
                                google_ad_slot = "0129757549";
                                google_ad_width = 300;
                                google_ad_height = 250;
                        //-->
                        </script>
                        <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                        </script>
                    </div>
                </div>
                
                <?php if( $module_demandes ){ ?>

                <div class="menu-widget">
                    <h5>Catégories en attente</h5>

                    <ul>
                    <?php $counter1=-1; if( isset($module_demandes) && is_array($module_demandes) && sizeof($module_demandes) ) foreach( $module_demandes as $key1 => $value1 ){ $counter1++; ?>

                        <li <?php if( $counter1 & 1 ){ ?>class="odd"<?php }else{ ?>class="even"<?php } ?>><a href="<?php echo $key1;?>" title="<?php echo $value1;?>"><?php echo résumer(40, $value1); ?></a></li>
                    <?php } ?>

                    </ul>
                </div>
                <?php } ?>

                
                <?php if( $changements ){ ?>

                <div class="menu-widget">
                    <h5>Dernières modifications</h5>

                    <ul>
                    <?php $counter1=-1; if( isset($changements) && is_array($changements) && sizeof($changements) ) foreach( $changements as $key1 => $value1 ){ $counter1++; ?>

                        <li <?php if( $counter1 & 1 ){ ?>class="odd"<?php }else{ ?>class="even"<?php } ?>><a href="<?php echo $key1;?>" title="<?php echo $value1;?>"><?php echo résumer(40, $value1); ?></a></li>
                    <?php } ?>

                    </ul>
                </div>
                <?php } ?>


            </aside>
        </div>
        <!-- Sidebar Content END -->
        
    </section>

    <!-- Testimonial Slider START -->
    <div class="container">
        <section class="testimonials">

            <div class="testimonials-inner">
                <ul id="testimonial-slider">

                    <li>
                        <blockquote>Spoils.fr, en tant que site de diffusion de résumés, n'est en rien propriétaire des oeuvres originales synthétisées.</blockquote>
                    </li>

                </ul>
            </div>

        </section>
    </div>
    <!-- Testimonial Slider END -->

    <!-- Footer START -->
    <div id="footer-top"></div>
    <footer class="centered">
            <div class="container">
                
                <div class="four columns divider-right">
                    <h1>Partenaires</h1>

                    <ul class="footer_list">
                        <?php $counter1=-1; if( isset($partenaires) && is_array($partenaires) && sizeof($partenaires) ) foreach( $partenaires as $key1 => $value1 ){ $counter1++; ?>

                            <li><a href="<?php echo $value1["url"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></li>
                        <?php } ?>

                    </ul>
                </div>

                <div class="four columns divider-right">
                    <h1>Statistiques</h1>

                    <p><?php echo $stats;?></p>
                </div>

                <div class="four columns">
                    <h1>Le site</h1>
                    <p>WIKI, avec système d'approbation, entièrement développé en PHP par LordPretender.</p>
                    <a href="http://feeds.feedburner.com/spoils/RqLB"><img class="icon" src="<?php echo $site_tpl;?>images/icons/rss.png" alt="" /></a>
                    <a href="http://www.facebook.com/SpoilsSpoils"><img class="icon" src="<?php echo $site_tpl;?>images/icons/facebook.png" alt="" /></a>
                    <a href="https://twitter.com/LordPretender"><img class="icon" src="<?php echo $site_tpl;?>images/icons/twitter.png" alt="" /></a>
                </div>

            </div>
    </footer>
    <div id="footer-bottom"></div>
    <!-- Footer END -->

    <!-- Logo and Copyright START -->
    <section id="bottom">
        <div class="container centered">

            <div class="five columns">
                <a href="index.html">
                    <img src="<?php echo $site_tpl;?>images/main/logo-footer.png" alt="" />
                </a>
                <p>Copyright &copy; 2012 - <?php echo date('Y'); ?></p>
            </div>

            <div class="seven columns">
                <ul>
                    <li><a href="/" title="Accueil">Accueil</a></li>
                    <?php $counter1=-1; if( isset($themes) && is_array($themes) && sizeof($themes) ) foreach( $themes as $key1 => $value1 ){ $counter1++; ?>

                    <li><a href="/<?php echo $key1;?>/" title="<?php echo $value1;?>"><?php echo $value1;?></a></li>
                    <?php } ?>

                </ul>
            </div>

        </div>
    </section>
    <!-- Logo and Copyright END -->
</body>
</html>