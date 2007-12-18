<?php

function action_db_creer_noms_sans_accents() {
	
	include_spip('inc/charsets');
	
	// Structure  pour le calcul des produits suggeres dans une combo
	spip_query("ALTER TABLE  `tbl_items`  ADD `nom_sans_accent` VARCHAR( 255 ) NOT NULL AFTER `nom`,
	ADD  `source` VARCHAR( 100 ) NOT NULL COMMENT  'de type 4' AFTER  `nom_sans_accent` ,
	ADD  `famille` VARCHAR( 100 ) NOT NULL COMMENT  'de type 2' AFTER  `source`,
	ADD  `source_sans_accent` VARCHAR( 100 ) NOT NULL AFTER  `source` ;");
	
	$q = spip_query('select nom, id_item from tbl_items');
	echo spip_num_rows($q).' LIGNES';
	while($r = spip_fetch_array($q))	{
		$chaine = translitteration($r['nom']);
		spip_query("update tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item']);	
		echo "update tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item'].";\n";	
	}	
	
	// Mise à jour pour le calcul des produits suggérés dans une combo
	spip_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.source = b.nom, a.source_sans_accent = b.nom_sans_accent
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 4
	and a.id_type_item IN (3,5)");
	
	spip_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.famille = b.nom
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 2
	and a.id_type_item IN (3,5)");
	
	// Optimisation pour le calcul des RC
	spip_query("ALTER TABLE  `tbl_reactions_croisees` ADD INDEX (  `id_produit1` )");
	spip_query("ALTER TABLE  `tbl_reactions_croisees` ADD INDEX (  `id_produit2` )");
	
	echo 'FINI !!';
}	

?>