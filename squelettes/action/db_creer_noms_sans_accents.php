<?php

function action_db_creer_noms_sans_accents() {

	set_time_limit(0);
	
	include_spip('inc/charsets');
	
	// Structure  pour le calcul des produits suggeres dans une combo
	// spip_query("ALTER TABLE  `tbl_items`  ADD `nom_sans_accent` VARCHAR( 255 ) NOT NULL AFTER `nom`");
	spip_query("ALTER TABLE  `tbl_items`  ADD `source` VARCHAR( 100 ) NOT NULL COMMENT  'de type 4' AFTER  `nom` ");
	spip_query("ALTER TABLE  `tbl_items`  ADD `famille` VARCHAR( 100 ) NOT NULL COMMENT  'de type 2' AFTER  `source`");
	spip_query("ALTER TABLE  `tbl_items`  ADD `source_sans_accent` VARCHAR( 100 ) NOT NULL AFTER  `source` ;");
	
	// Mise Ã  jour pour le calcul des produits suggÃ©rÃ©s dans une combo
	spip_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.source = b.nom, a.source_sans_accent = b.nom_court
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 4
	and NOT (b.nom LIKE '%spp.' OR b.nom LIKE '%toutes%')
	");
	
	spip_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.famille = b.nom
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 2
	");
	
	// Optimisation pour le calcul des RC
	spip_query("ALTER TABLE  `tbl_reactions_croisees` ADD INDEX (  `id_produit1` )");
	spip_query("ALTER TABLE  `tbl_reactions_croisees` ADD INDEX (  `id_produit2` )");
	spip_query("ALTER TABLE  `tbl_est_dans` ADD INDEX (  `id_item` )");
	spip_query("ALTER TABLE  `tbl_est_dans` ADD INDEX (  `est_dans_id_item` )");
	
	// Création de la table de cache des requêtes
	spip_query("DROP TABLE IF EXISTS `cache_requetes`;");
	spip_query("CREATE TABLE `cache_requetes` (
  `id` bigint(20) NOT NULL auto_increment,
  `page` varchar(30) NOT NULL COMMENT 'la page json',
  `tuple` varchar(50) NOT NULL COMMENT 'le tuple ordonne',
  `hash` varchar(50) NOT NULL COMMENT 'le md5 de la requete si elle est contextuelle',
  `resultat_json` text NOT NULL COMMENT 'le texte a retourner',
  `date_maj` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page` (`page`,`tuple`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='pour ne pas recalculer les requetes json ' ;");
	
	
	echo ('FINI !!');
}	

?>
