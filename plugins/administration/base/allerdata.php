<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


function allerdata_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	//$interface['table_des_tables']['items']='tbl_items';
	
	//-- Jointures ----------------------------------------------------
	/*$interface['tables_jointures']['spip_evenements'][]= 'mots'; // a placer avant la jointure sur articles
	$interface['tables_jointures']['spip_articles'][]= 'evenements';
	$interface['tables_jointures']['spip_evenements'][] = 'articles';
	$interface['tables_jointures']['spip_mots'][]= 'mots_evenements';
	$interface['tables_jointures']['spip_evenements'][] = 'mots_evenements';

	$interface['table_des_traitements']['LIEU'][]= 'propre(%s)';
	
	// permet d'utiliser les criteres racine, meme_parent, id_parent
	$interface['exceptions_des_tables']['evenements']['id_parent']='id_evenement_source';
	$interface['exceptions_des_tables']['evenements']['id_rubrique']=array('spip_articles', 'id_rubrique');
		
	$interface['table_date']['evenements'] = 'date_debut';*/


	$interface['table_des_traitements']['COMMENTAIRES'][]= _TRAITEMENT_RACCOURCIS;

	return $interface;
}

function allerdata_declarer_tables_principales($tables_principales){
  
	//-- Table tbl_items ------------------------------------------
	$items = array(
	  'id_item' => "int(11) NOT NULL",
	  'id_type_item' => "int(11) default NULL",
	  'nom_fr' => "varchar(255) default NULL",
	  'nom_en' => "varchar(255) default NULL",
	  'nom_anglosaxon' => "varchar(255) default NULL",
#	  'source' => "varchar(100) NOT NULL",
#	  'source_sans_accent' => "varchar(100) NOT NULL",
#	  'famille' => "varchar(100) NOT NULL",
	  'autre_nom_fr' => "varchar(255) default NULL",
	  'autre_nom_en' => "varchar(255) default NULL",
	  'nom_complet_fr' => "varchar(255) default NULL",
	  'nom_complet_en' => "varchar(255) default NULL",
	  'chaine_alpha_fr' => "varchar(255) default NULL",
	  'chaine_alpha_en' => "varchar(255) default NULL",
	  'interrogeable' => "tinyint(1) default NULL",
	  'testable' => "tinyint(1) default NULL",
	  'code_test' => "varchar(50) default NULL",
	  'iuis' => "tinyint(1) default NULL",
	  'masse' => "varchar(50) default NULL",
	  'glyco' => "varchar(50) default NULL",
	  'id_niveau_allergenicite' => "int(11) default NULL",
	  'date_item' => "datetime default NULL",
	  'affichage_suggestion' => "tinyint(1) default NULL",
	  'representatif_fr' => "varchar(50) default NULL",
	  'representatif_en' => "varchar(50) default NULL",
	  'ccd_possible' => "tinyint(1) default NULL",
	  'information' => "tinyint(1) default NULL",
	  'fonction_classification_fr' => "varchar(50) default NULL",
	  'fonction_classification_en' => "varchar(50) default NULL",
	  'nom_court_fr' => "varchar(100) default NULL",
	  'nom_court_en' => "varchar(100) default NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		'remarques' => "text default NULL",
	  'url' => "varchar(255) default NULL",
		"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
  );
	
	$items_key = array(
			"PRIMARY KEY"	=> "id_item",
			"KEY code_test"	=> "code_test",
			"KEY id_type_item"	=> "id_type_item",
			);
	
	$tables_principales['tbl_items'] =
		array('field' => &$items, 'key' => &$items_key);


	return $tables_principales;
}

function allerdata_declarer_tables_auxiliaires($tables_auxiliaires){
	$tbl_est_dans = array(
		'id_est_dans' => "int(11) NOT NULL auto_increment",
		'id_item' => "int(11) default NULL",
		'est_dans_id_item' => "int(11) default NULL",
		'date_est_dans' => "datetime default NULL",
		'directement_contenu' => "tinyint(1) default '0'",
	);
	
	$tbl_est_dans_key = array(
			"PRIMARY KEY"	=> "id_est_dans",
			"KEY couple"	=> "id_item,est_dans_id_item",
			"KEY id_item"	=> "id_item",
			"KEY est_dans_id_item"	=> "est_dans_id_item",
	);

	$tables_auxiliaires['tbl_est_dans'] = array(
		'field' => &$tbl_est_dans,
		'key' => &$tbl_est_dans_key);
 	
	$tbl_types_items = array(
		"id_type_item" => "int(11) NOT NULL auto_increment",
		"nom_type_item" => "varchar(50) default NULL",
		"liste_choix" => "tinyint(1) default NULL",
		"pentacle" => "tinyint(1) default NULL",
		"RC_type5" => "tinyint(1) default NULL",
		"RC_type3" => "tinyint(1) default NULL",
		"croise-egalement" => "tinyint(1) default NULL",
		"CCD_possible" => "tinyint(1) default NULL",
		"tableau_allergenes" => "tinyint(1) default NULL",
		"popup_produits_type3" => "tinyint(1) default NULL",
		"rq_type_item" => "longtext",
	);	
	$tbl_types_items_key = array(
		"PRIMARY KEY"=> "id_type_item",
	);
	$tables_auxiliaires['tbl_types_items'] = array(
		'field' => &$tbl_types_items,
		'key' => &$tbl_types_items_key);
	
	$tbl_niveaux_allergenicite = array(
  "id_niveau_allergenicite" => "int(11) NOT NULL",
  "niveau_de_preuve" => "varchar(50) default NULL",
  );
	$tbl_niveaux_allergenicite_key = array(
		"PRIMARY KEY"=> "id_niveau_allergenicite",
	);
	$tables_auxiliaires['tbl_niveaux_allergenicite'] = array(
		'field' => &$tbl_niveaux_allergenicite,
		'key' => &$tbl_niveaux_allergenicite_key);

	$tbl_items_versions = array(
	  'id_item' => "int(11) NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"commentaires" => "text",
		"diff" => "text", // liste des champs modifies avant/apres
		"vu_id_auteur"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"vu_date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
  );
	$tbl_items_versions_key = array (
			"PRIMARY KEY"	=> "id_item, id_version");
	$tables_auxiliaires['tbl_items_versions'] = array(
		'field' => &$tbl_items_versions,
		'key' => &$tbl_items_versions_key);

	return $tables_auxiliaires;
}

?>