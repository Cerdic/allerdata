<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


function biblio_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	$interface['table_des_tables']['journals']='tbl_journals';
	$interface['table_des_tables']['bibilographies']='tbl_bibilographies';
	
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

function biblio_declarer_tables_principales($tables_principales){
  
	//-- Table tbl_journals ------------------------------------------
	// une grosse faute de francais pour rester spipien ...
	$journals = array(
	  'id_journal' => "int(11) NOT NULL",
	  'nom' => "varchar(255) default NULL",
  );
	
	$journals_key = array(
			"PRIMARY KEY"	=> "id_journal",
			"KEY nom"	=> "nom",
			);
	
	$tables_principales['tbl_journals'] =
		array('field' => &$journals, 'key' => &$journals_key);

	//-- Table tbl_bibliographies ------------------------------------------
	$bibliographies = array(
		'id_bibliographie' => "int(11) NOT NULL",
		"auteurs"	=> "text DEFAULT '' NOT NULL",
		"titre"	=> "text DEFAULT '' NOT NULL",
		'id_journal' => "int(11) NOT NULL",
		'annee' => "int(11) NOT NULL",
		'volume'=>"varchar(10) DEFAULT ''",
		'premiere_page'=>"varchar(10) DEFAULT ''",
		'derniere_page'=>"varchar(10) DEFAULT ''",
		'numero'=>"varchar(10) DEFAULT ''",
		'supplement'=>"varchar(10) DEFAULT ''",
		"url"	=> "VARCHAR(255) DEFAULT '' NOT NULL",
		"autre_media"	=> "longtext DEFAULT '' NOT NULL",
		"abstract"	=> "longtext DEFAULT '' NOT NULL",
		"url_full_text"	=> "VARCHAR(255) DEFAULT '' NOT NULL",
		'full_text_disponible' => "tinyint(1) DEFAULT 0 NOT NULL",
		'sans_interet' => "tinyint(1) DEFAULT 0 NOT NULL",
		'citation' => "text DEFAULT '' NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		'date' => "datetime default NULL",
	);
	
	$bibliographies_key = array(
			"PRIMARY KEY"	=> "id_bibliographie",
			"index"	=> "id_journal",
	);
	
	$tables_principales['tbl_bibliographies'] =
		array('field' => &$bibliographies, 'key' => &$bibliographies_key);


	return $tables_principales;
}

function biblio_declarer_tables_auxiliaires($tables_auxiliaires){
	/*$tbl_est_dans = array(
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
  );
	$tbl_items_versions_key = array (
			"PRIMARY KEY"	=> "id_item, id_version");
	$tables_auxiliaires['tbl_items_versions'] = array(
		'field' => &$tbl_items_versions,
		'key' => &$tbl_items_versions_key);*/

	return $tables_auxiliaires;
}

?>