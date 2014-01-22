<?php if(!class_exists('raintpl')){exit;}?>                                    <?php if( $msg_type != '' ){ ?><p class="<?php echo $msg_type;?>"><?php echo $msg_text;?></p><?php } ?>


                                    <p>Vous pouvez nous contacter à l'aide du formulaire ci-dessous. N'hésitez pas à nous poser vos questions et proposer vos remarques.</p>

                                    <form class="main-form" id="email-form" action="<?php echo $fichier_url;?>" method="post">
                                        <fieldset>
                                            <input type="text" class="text-field" tabindex="1" value="" id="name" name="name" placeholder="Votre nom*" required />
                                            <input type="email" class="text-field" tabindex="2" value="" id="email" name="email" placeholder="Votre Email*" required />
                                            <select name="type" tabindex="3">
                                            <?php $counter1=-1; if( isset($mail_type) && is_array($mail_type) && sizeof($mail_type) ) foreach( $mail_type as $key1 => $value1 ){ $counter1++; ?>

                                                <option><?php echo $value1;?></option>
                                            <?php } ?>

                                            </select>
                                            <input type="text" class="text-field" tabindex="4" value="" id="subject" name="subject" placeholder="Sujet*" required />
                                            <textarea id="message" cols="50" rows="10" tabindex="5" class="text-area" name="message" placeholder="Votre message*" required></textarea>
                                            <input type="text" class="text-field" tabindex="6" value="" id="spam_check" name="spam_check" placeholder="Laisser ce champ vide*" />
                                            <input type="submit" id="comment-submit" tabindex="7" value="Envoyer">
                                            <div id="outcome"></div>
                                        </fieldset>
                                    </form>