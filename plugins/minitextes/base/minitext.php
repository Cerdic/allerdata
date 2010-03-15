<?php
/*
 * Plugin Minitext / Admin des mini-textes
 * Licence GPL
 * (c) 2010 C.Morin Yterium pour Allerdata SARL
 *
 */

function minitext_upgrade($nom_meta_base_version,$version_cible){
	$current_version = 0.0;
	if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
		if (version_compare($current_version,'0.1.0.0','<')){
			include_spip('base/abstract_sql');
			include_spip('base/serial');
			include_spip('base/aux');
			include_spip('base/create');

			maj_tables('tbl_minitextes');
			maj_tables('tbl_minitextes_items');
			maj_tables('tbl_items');

			ecrire_meta($nom_meta_base_version,$current_version='0.1.0.0','non');
		}
	}
}

function minitext_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}


function minitext_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	$interface['table_des_tables']['minitextes']='tbl_minitextes';

	$interface['tables_jointures']['tbl_items'][]= 'tbl_minitextes';
	$interface['tables_jointures']['tbl_minitextes'][]= 'tbl_items';

	return $interface;
}

function minitext_declarer_tables_principales($tables_principales){
  
	//-- Table tbl_journals ------------------------------------------
	// une grosse faute de francais pour rester spipien ...
	$minitextes = array(
	  'id_minitexte' => "bigint(21) NOT NULL",
		"texte"	=> "longtext DEFAULT '' NOT NULL",
		'incidence_rav' => "bigint(21) NOT NULL",
  );
	
	$minitextes_key = array(
			"PRIMARY KEY"	=> "id_minitexte",
			);
	
	$tables_principales['tbl_minitextes'] =
		array('field' => &$minitextes, 'key' => &$minitextes_key);


	$tables_principales['tbl_items']['field']['id_minitexte'] = "bigint(21) NOT NULL";

	return $tables_principales;
}

function minitext_declarer_tables_auxiliaires($tables_auxiliaires){
	$minitextes_items = array(
			"id_minitexte"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"id_item_1"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"id_item_2"	=> "bigint(21) DEFAULT '0' NOT NULL",
	);
	
	$minitextes_items_key = array(
			"PRIMARY KEY"	=> "id_minitexte, id_item_1, id_item_2",
			"KEY id_item_1"	=> "id_item_1",
			"KEY id_item_2"	=> "id_item_2",
	);
	$tables_auxiliaires['tbl_minitextes_items'] =
		array('field' => &$minitextes_items, 'key' => &$minitextes_items_key);

	
	return $tables_auxiliaires;
}

?>