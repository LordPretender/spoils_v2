<?php if(!class_exists('raintpl')){exit;}?>                                <p><?php echo $fichier_contenu;?></p>
                                
                                <?php if( $fichier_slug != '' ){ ?>

                                <?php if( $fichier_type > 0 ){ ?>

                                <div class="divider"></div>
                                <?php $counter1=-1; if( isset($pages) && is_array($pages) && sizeof($pages) ) foreach( $pages as $key1 => $value1 ){ $counter1++; ?>

                                <div class="blog-post">
                                    <h2 class="centered"><a href="<?php echo $value1["lien"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></h2>

                                    <p><?php echo $value1["description"];?></p>
                                </div>
                                <?php } ?>

                                <?php echo $pagination;?>

                                <?php }else{ ?>

                                <span class='st_fblike_hcount' displayText='Facebook Like'></span>
                                <span class='st_plusone_hcount' displayText='Google +1'></span>
                                <span class='st_twitter_hcount' displayText='Tweet'></span>
                                <span class='st_email_hcount' displayText='Email'></span>
                                <script type="text/javascript">var switchTo5x=true;</script>
                                <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
                                <script type="text/javascript">stLight.options({publisher: "eaa7ca68-c03e-4e32-9aad-500d6eb9b676"}); </script>
                                <?php } ?>

                                
                                <?php if( $pageSpeciale != 1 ){ ?>

                                <h1 class="special"><span>Informations supplémentaires</span></h1>
                                <ul class="list-red">
                                <?php $counter1=-1; if( isset($info_supp) && is_array($info_supp) && sizeof($info_supp) ) foreach( $info_supp as $key1 => $value1 ){ $counter1++; ?>

                                    <li><?php echo $key1;?> : <a href="<?php echo $value1["slug"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></li>
                                <?php } ?>

                                    <li>Collaborateurs : <?php echo $fichier_contributeurs;?></li>
                                </ul>
                                <?php } ?>

                                <?php }else{ ?>

                                <h1 class="special"><span>Dernières contributions</span></h1>
                                <?php $counter1=-1; if( isset($pages) && is_array($pages) && sizeof($pages) ) foreach( $pages as $key1 => $value1 ){ $counter1++; ?>

                                <div class="blog-post">
                                    <h2 class="centered"><a href="<?php echo $value1["lien"];?>" title="<?php echo $value1["titre"];?>"><?php echo $value1["titre"];?></a></h2>

                                    <p><?php echo $value1["description"];?></p>
                                </div>
                                <?php } ?>

                                <?php } ?>