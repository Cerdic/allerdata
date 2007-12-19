<?php
function action_parents_differents(){

	set_time_limit(0);
	
	$query_liste_produits = "SELECT tbl_reactions_croisees.id_reaction_croisee, tbl_reactions_croisees.id_produit1, tbl_reactions_croisees.id_produit2 FROM tbl_reactions_croisees ORDER By tbl_reactions_croisees.id_produit1";
	$liste_produits = spip_query($query_liste_produits, $allerdata) or die(mysql_error());
	$row_liste_produits = mysql_fetch_assoc($liste_produits);
	$totalRows_liste_produits = mysql_num_rows($liste_produits);

	echo 'Ca commence !<br />';

	do {
		//on récupère l'id des produits concerné et l'id de la relation concernée
		$id_produit1 = $row_liste_produits['id_produit1'];
		$id_produit2 = $row_liste_produits['id_produit2'];
		$id_reaction_croise = $row_liste_produits['id_reaction_croisee'];
		
		//on séléctionne ses parents de type pentacle.
		
		$query_liste_parents_produit1 = "SELECT tbl_est_dans.est_dans_id_item FROM tbl_items, tbl_types_items, tbl_est_dans
								WHERE tbl_est_dans.id_item = '$id_produit1' AND tbl_items.id_item = tbl_est_dans.est_dans_id_item
								AND tbl_items.id_type_item = tbl_types_items.id_type_item AND tbl_types_items.pentacle = '1'";
		$liste_parents_produit1 = spip_query($query_liste_parents_produit1, $allerdata) or die(mysql_error());
		$row_liste_parents_produit1 = mysql_fetch_assoc($liste_parents_produit1);
		$totalRows_liste_parents_produit1 = mysql_num_rows($liste_parents_produit1);
		
		$update = 0;
		//on boucle sur les parents de type pentacle de produit 1
		do {
			if ($update != 1) {
				$id_parent_produit1 = $row_liste_parents_produit1['est_dans_id_item'];
				
				//on vérifie si ce parent de produit 1 est également un parent de produit 2
				
				$query_liste_parents_produit2 = "SELECT tbl_est_dans.est_dans_id_item FROM tbl_items, tbl_types_items, tbl_est_dans
										WHERE tbl_est_dans.id_item = '$id_produit2' AND tbl_items.id_item = tbl_est_dans.est_dans_id_item
										AND tbl_items.id_type_item = tbl_types_items.id_type_item AND tbl_types_items.pentacle = '1'
										AND tbl_items.id_item = '$id_parent_produit1'";
				$liste_parents_produit2 = spip_query($query_liste_parents_produit2, $allerdata) or die(mysql_error());
				$row_liste_parents_produit2 = mysql_fetch_assoc($liste_parents_produit2);
				$totalRows_liste_parents_produit2 = mysql_num_rows($liste_parents_produit2);
				
				//si on a un parent unique pour les deux produits
				if ($totalRows_liste_parents_produit2 > 0) { 
					//on s'assure qu'il n'y aura pas d'autres recherche
					$update = 1;
				}
				//si les parents sont différents
				else { 	}
			}
		} while ($row_liste_parents_produit1 = mysql_fetch_assoc($liste_parents_produit1));

		if ($update > 0) {
			//on met à jour la bdd pour dire que la reaction concerne des parents commun
			
			$query_identique = "UPDATE tbl_reactions_croisees set produits_differents ='0'
								WHERE id_reaction_croisee = '$id_reaction_croise'";
			spip_query($query_identique, $allerdata) or die(mysql_error());			
		}
		else {
			//on met à jour la bdd pour dire que la reaction ne concerne pas des parents commun
			
			$query_identique = "UPDATE tbl_reactions_croisees set produits_differents ='1'
								WHERE id_reaction_croisee = '$id_reaction_croise'";
			spip_query($query_identique, $allerdata) or die(mysql_error());					
		}

	} while ($row_liste_produits = mysql_fetch_assoc($liste_produits));

	echo 'Termine ;-)';
}
?>