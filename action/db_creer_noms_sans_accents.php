<?php

function action_db_creer_noms_sans_accents() {

	set_time_limit(0);
	
	include_spip('inc/charsets');
	
	// Structure  pour le calcul des produits suggeres dans une combo
	spip_query("ALTER TABLE  `tbl_items`  ADD `nom_sans_accent` VARCHAR( 255 ) NOT NULL AFTER `nom`");
	spip_query("ALTER TABLE  `tbl_items`  ADD `source` VARCHAR( 100 ) NOT NULL COMMENT  'de type 4' AFTER  `nom_sans_accent` ");
	spip_query("ALTER TABLE  `tbl_items`  ADD `famille` VARCHAR( 100 ) NOT NULL COMMENT  'de type 2' AFTER  `source`");
	spip_query("ALTER TABLE  `tbl_items`  ADD `source_sans_accent` VARCHAR( 100 ) NOT NULL AFTER  `source` ;");
	
	$q = spip_query('select nom, id_item from tbl_items');
	echo spip_num_rows($q).' LIGNES <ul>';
	ob_flush(); flush(); usleep(50);
	while($r = spip_fetch_array($q))	{
		$chaine = translitteration($r['nom']);
		$chaine = str_replace('(s)','', $chaine);
		$chaine = str_replace('(toutes especes)','', $chaine);
		$chaine = str_replace('toutes especes','', $chaine);
		$chaine = str_replace('toutes varietes','', $chaine);
		$chaine = str_replace("d'",'', $chaine);
		$chaine = str_replace('(',' ', $chaine);
		$chaine = str_replace(')',' ', $chaine);
		
		spip_query("update tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item']);	
		echo "<li>".addslashes($chaine)." (".$r['id_item'].")</li>";	
		ob_flush(); flush(); usleep(500);
	}
	
	echo '</ul>';
	
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
	spip_query("ALTER TABLE  `tbl_est_dans` ADD INDEX (  `id_item` )");
	spip_query("ALTER TABLE  `tbl_est_dans` ADD INDEX (  `est_dans_id_item` )");
	
	spip_query("DROP TABLE IF EXISTS tbl_index_items");
	spip_query("CREATE TABLE `tbl_index_items` (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`id_item` INT NOT NULL ,
	`keyword` VARCHAR( 255 ) NOT NULL ,
	INDEX ( `id_item` , `keyword` ) ,
	FULLTEXT (
	`keyword` 
	)
	) ENGINE = MYISAM COMMENT = 'extraits de nom, nom_sans_accent, source, source_sans_accent';");
	
	$stopwords = array("(",")",",","toutes","espèces","spp.",'/','aux','des');
	// On alimente la table d'indexation
	$q = spip_query("select id_item, nom, nom_sans_accent, source, source_sans_accent from tbl_items where id_type_item IN (3,5)");
	echo "INDEXATION : <br />";
	$i = 0;
	while($row = spip_fetch_array($q)) {
		$t_insert = array();
		$i++;
		$ph = $row['nom'].' '.$row['nom_sans_accent'].' '.$row['source'].' '.$row['source_sans_accent'];
		$ph = str_replace($stopwords,'',trim(ereg_replace("[[:space:]]+",' ',$ph)));
		$t_index = explode(' ',$ph);
		foreach ($t_index as $keyword) {
			if (strlen($keyword)>=3) {
				$ch = '(NULL,'.$row['id_item'].',"'.mysql_real_escape_string(strtolower($keyword)).'")';
				if (!in_array($ch, $t_insert)) $t_insert[] = $ch;
			}
		}
		if (sizeof($t_insert)) {
			$query = "INSERT INTO `allerdata`.`tbl_index_items` (`id`, `id_item`, `keyword`)
					VALUES ".implode(',',$t_insert).";";
			echo('<hr>'.$query);
			spip_query($query);
			ob_flush(); flush(); usleep(50);
		}
	}
	
	echo ('FINI !!');
}	

?>
