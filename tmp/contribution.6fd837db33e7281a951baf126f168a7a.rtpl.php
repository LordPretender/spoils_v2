<?php if(!class_exists('raintpl')){exit;}?>                                    <?php if( $mode > 0 ){ ?>

                                    <p><?php echo $fichier_contenu;?></p>
                                    <div class="divider"></div>
                                    <?php } ?>

									<?php if( $visiteur ){ ?>

									<p>Mémorisez votre login et votre email car ils vous seront demandés pour toutes les prochaines participations et ils devront correspondre.</p>
									<p>Savez-vous qu'en vous identifiant sur le forum (via "Critiques / Discussions"), il ne vous sera plus demandé de saisir votre login et votre email ?</p>
									<p>
										Lors de votre inscription, veillez à utiliser le même login et la même adresse e-mail car, sinon, vous risquez de rencontrer des soucis d'intégration de vos précédentes contributions.
										<br />Dans le pire des cas, vous ne pourrez plus effectuer de demandes.
									</p>
									<p>Si vous rencontrez des soucis, n'hésitez pas à contacter l'administrateur ou à poser votre question sur le forum.</p>
									<?php } ?>

                                    <?php if( $mode_approbation ){ ?>

                                    <p>
                                        Informations concernant la demande :
                                        <ul class="list-red">
                                            <li>Date de création : <?php echo $demande_date;?></li>
                                            <li>IP du demandeur : <?php echo $demande_ip;?></li>
                                            <li>Type de la demande : <?php echo $demande_mode;?></li>
                                            <li>Lien de la page <?php if( $mode > 0 ){ ?>parente<?php }else{ ?>à modifier<?php } ?> : <a href="<?php echo $demande_url;?>" title="<?php echo $demande_url;?>"><?php echo $demande_url;?></a></li>
                                        </ul>
                                    </p>
                                    <p>Merci de renseigner le login et l'adresse mail de la personne qui a envoyé cette demande.</p>
                                    <div class="divider"></div>
                                    <?php } ?>

                                    <?php if( $msg_type != '' ){ ?><p class="<?php echo $msg_type;?>"><?php echo $msg_text;?></p><?php } ?>


                                    <form method="post" action="<?php echo $formulaire;?>" class="main-form">
                                        <fieldset>
											<?php if( $visiteur || $mode_approbation ){ ?>

                                            <input type="text" class="text-field" tabindex="1" value="<?php echo $formulaire_login;?>" id="login" name="login" placeholder="Login*" required autofocus />
                                            <input type="email" class="text-field" tabindex="2" value="<?php echo $formulaire_email;?>" id="email" name="email" placeholder="Votre Email*" required />
											<?php } ?>

											
                                            <input type="text" class="text-field" tabindex="3" value="<?php echo $formulaire_titre;?>" id="titre" name="titre" placeholder="Titre*" required />
											
                                            <?php if( $mode_approbation && $mode > 0 ){ ?><input type="text" class="text-field" tabindex="4" value="" id="slug" name="slug" placeholder="Slug*" required /><?php } ?>

											
											<?php if( $mode > 1 ){ ?>

											<label for="type_contenu">Type de pages de la future catégorie :</label>
											<select name="type_contenu" id="type_contenu">
												<option value="-1"<?php if( $type_choisi == -1 ){ ?> selected<?php } ?>>Aucun type</option>
												<?php $counter1=-1; if( isset($type_contenu) && is_array($type_contenu) && sizeof($type_contenu) ) foreach( $type_contenu as $key1 => $value1 ){ $counter1++; ?>

												<option value="<?php echo $key1;?>"<?php if( $type_choisi == $key1 ){ ?> selected<?php } ?>><?php echo $value1;?></option>
						                        <?php } ?>

											</select>
											<?php } ?>

											
                                            <textarea id="spoil" cols="50" rows="10" tabindex="7" class="text-area" name="spoil" placeholder="Votre Spoil*" required><?php echo $formulaire_spoil;?></textarea>

                                            <input type="text" class="text-field" tabindex="8" value="" id="spam_check" name="spam_check" placeholder="Laisser ce champ vide*" />

                                            <input type="hidden" name="origine" value="<?php echo $formulaire_slug;?>" />
                                            <input type="submit" id="comment-submit" tabindex="9" value="Envoyer">
                                            
                                            <div id="outcome"></div>
                                        </fieldset>
                                    </form>