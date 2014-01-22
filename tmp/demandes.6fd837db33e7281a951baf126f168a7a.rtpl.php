<?php if(!class_exists('raintpl')){exit;}?>                    	            <p><?php echo $fichier_contenu;?></p>
									<table>
										<tr>
											<th width="5%">#</th>
											<th width="20%">Titre</th>
											<th width="20%">Login</th>
											<th width="15%">IP</th>
											<th width="20%">Type</th>
											<th width="20%">Date</th>
										</tr>
										
		                                <?php $counter1=-1; if( isset($demandes) && is_array($demandes) && sizeof($demandes) ) foreach( $demandes as $key1 => $value1 ){ $counter1++; ?>

										<tr>
											<td><?php echo $key1;?></td>
											<td><?php echo $value1["titre"];?></td>
											<td><?php echo $value1["login"];?></td>
											<td><?php echo $value1["ip"];?></td>
											<td><?php echo $value1["mode"];?></td>
											<td><?php echo $value1["date"];?></td>
										</tr>
        		                        <?php } ?>

									</table>