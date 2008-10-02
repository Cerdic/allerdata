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

	return $interface;
}

function allerdata_declarer_tables_principales($tables_principales){
  
	//-- Table tbl_items ------------------------------------------
	$items = array(
	  'id_item' => "int(11) NOT NULL",
	  'id_type_item' => "int(11) default NULL",
	  'nom' => "varchar(255) default NULL",
	  'source' => "varchar(100) NOT NULL",
	  'source_sans_accent' => "varchar(100) NOT NULL",
	  'famille' => "varchar(100) NOT NULL
	  'autre_nom' => varchar(255) default NULL",
	  'nom_complet' => "varchar(255) default NULL",
	  'chaine_alpha' => "varchar(255) default NULL",
	  'interrogeable' => "tinyint(1) default NULL",
	  'testable' => "tinyint(1) default NULL",
	  'code_test' => "varchar(50) default NULL",
	  'iuis' => "tinyint(1) default NULL",
	  'masse' => "varchar(50) default NULL",
	  'glyco' => "varchar(50) default NULL",
	  'id_niveau_allergenicite' => "int(11) default NULL",
	  'date_item' => "datetime default NULL",
	  'affichage_suggestion' => "tinyint(1) default NULL",
	  'representatif' => "varchar(50) default NULL",
	  'ccd_possible' => "tinyint(1) default NULL",
	  'information' => "tinyint(1) default NULL",
	  'fonction_classification' => "varchar(50) default NULL",
	  'nom_court' => "varchar(50) default NULL"
  );
	
	$items_key = array(
			"PRIMARY KEY"	=> "id_item",
			"KEY code_test"	=> "code_test",
			"KEY id_type_item"	=> "id_type_item",
			);
	
	$tables_principales['tbl_items'] =
		array('field' => "&$items, 'key' => &$items_key");


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
		'field' => "&$tbl_est_dans,
		'key' => "&$tbl_est_dans_key);
		
	return $tables_auxiliaires;
}

?>