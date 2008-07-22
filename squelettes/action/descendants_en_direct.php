<?php
function action_descendants_en_direct() {
	spip_query("alter table `tbl_est_dans` add index `couple` (`id_item`, `est_dans_id_item`)");
	spip_query("alter table `tbl_est_dans` add index `id_item` (`id_item`)");
	spip_query("alter table `tbl_est_dans` add index `est_dans_id_item` (`est_dans_id_item`)");
	spip_query("alter table `tbl_items` add index `id_type_item` (`id_type_item`)");
	spip_query("update tbl_est_dans set directement_contenu=1 where date_est_dans<>0");

	set_time_limit(0);

	$query_liste_items = "SELECT tbl_items.id_item FROM tbl_items";
	$liste_items = spip_query($query_liste_items, $allerdata) or die(mysql_error());
	$row_liste_items = mysql_fetch_assoc($liste_items);
	$totalRows_liste_items = mysql_num_rows($liste_items);

	//on boucle sur le résultat
	echo 'Ca commence !<br />';
	do {
		//on récupère l'id de l'item en cours
		$id_item2 = $row_liste_items['id_item'];
		$id_item25 = $id_item2;
		
		// Creer la filiation sur soi-meme !
		create_filiation($id_item2,$id_item25);
		
		//on lance la fonction qui recherche les enfants directs avec l'id de l'item en cours.
		$liste_items2 = ListeEnfants("$id_item2","$id_item25");
	} while ($row_liste_items = mysql_fetch_assoc($liste_items));

	echo 'Termine ;-)';
}

//Fonction de recherche des enfants direct
function ListeEnfants($id_item,$id_parent) {
	
	//on recherche les enfants direct de l'item utilisé pour appeler la fonction.
	$query_liste_enfants = "SELECT tbl_est_dans.id_item FROM tbl_est_dans 
					WHERE tbl_est_dans.est_dans_id_item = '$id_item'
					AND tbl_est_dans.id_item != tbl_est_dans.est_dans_id_item";
	$liste_enfants = spip_query($query_liste_enfants) or die(mysql_error());
	$row_liste_enfants = mysql_fetch_assoc($liste_enfants);
	$totalRows_liste_enfants = mysql_num_rows($liste_enfants);
	
	//on boucle sur tous les enfants de cet item.
	do {
		$id_item1 = $row_liste_enfants['id_item'];
		
		//dans le cas où il y'a un enfant on continu le traitement
		if ($id_item1 != NULL) {
			
			create_filiation($id_item1,$id_parent);

			//On lance la fonction de recherche d'enfant pour ce nouvel item.
			$liste_enfants1 = ListeEnfants("$id_item1","$id_parent");

		}
	} while ($row_liste_enfants = mysql_fetch_assoc($liste_enfants));
}

function create_filiation($id_item1,$id_parent) {
		//on recherche dans la bdd si la liaison existe déjà entre le père principal et cet enfant
		$query_new_parent = "SELECT tbl_est_dans.id_est_dans FROM tbl_est_dans
							WHERE tbl_est_dans.id_item = '$id_item1' AND tbl_est_dans.est_dans_id_item = '$id_parent'";
		$new_parent = spip_query($query_new_parent) or die(mysql_error());
		$row_new_parent = mysql_fetch_assoc($new_parent);
		$totalRows_new_parent = mysql_num_rows($new_parent);
		
		//si une liaison existe on ne fait rien
		if ($totalRows_new_parent > 0) { }
		
		//si la filliation n'est pas enregistré, on insère un nouvel enregistrement avec un lien indirect.
		else { 
			$query_est_dans_indirect = "INSERT INTO tbl_est_dans (id_item,est_dans_id_item,directement_contenu) values('$id_item1','$id_parent','0')";
			spip_query($query_est_dans_indirect) or die(mysql_error());
			echo $id_item1.','.$id_parent.'<br />';
		}
}

?>