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
	$interface['table_des_tables']['bibliographies']='tbl_bibilographies';
	
	$interface['tables_jointures']['spip_articles'][]= 'bibliographies_articles';
	$interface['tables_jointures']['tbl_bibliographies'][]= 'bibliographies_articles';
	$interface['tables_jointures']['tbl_bibliographies'][]= 'tbl_bibliographies_versions';
	
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
	
	$interface['table_des_traitements']['ABSTRACT'][]= _TRAITEMENT_RACCOURCIS;

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
		"doublons_refs"	=> "VARCHAR(255) DEFAULT '' NOT NULL",
		"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
	);
	
	$bibliographies_key = array(
			"PRIMARY KEY"	=> "id_bibliographie",
			"index"	=> "id_journal",
	);
	
	$tables_principales['tbl_bibliographies'] =
		array('field' => &$bibliographies, 'key' => &$bibliographies_key);

	$biblio_notes = array(
		'id_biblio_note' => "bigint(21) NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		'id_bibliographie' => "int(11) NOT NULL",
		"texte"	=> "longtext DEFAULT '' NOT NULL",
		'date' => "datetime default NULL",
	);
	
	$biblio_notes_key = array(
			"PRIMARY KEY"	=> "id_biblio_note",
			"index"	=> "id_bibliographie",
	);
	
	$tables_principales['tbl_biblio_notes'] =
		array('field' => &$biblio_notes, 'key' => &$biblio_notes_key);

	return $tables_principales;
}

function biblio_declarer_tables_auxiliaires($tables_auxiliaires){
	$spip_bibliographies_articles = array(
			"id_bibliographie"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"id_article"	=> "bigint(21) DEFAULT '0' NOT NULL"
	);
	
	$spip_bibliographies_articles_key = array(
			"PRIMARY KEY"	=> "id_article, id_bibliographie",
			"KEY id_bibliographie"	=> "id_bibliographie"
	);
	$tables_auxiliaires['spip_bibliographies_articles'] =
		array('field' => &$spip_bibliographies_articles, 'key' => &$spip_bibliographies_articles_key);
		
	$tbl_bibliographies_versions = array(
	  'id_bibliographie' => "int(11) NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"commentaires" => "text",
		"diff" => "text", // liste des champs modifies avant/apres
  );
	$tbl_bibliographies_versions_key = array (
			"PRIMARY KEY"	=> "id_bibliographie, id_version");
	$tables_auxiliaires['tbl_bibliographies_versions'] = array(
		'field' => &$tbl_bibliographies_versions,
		'key' => &$tbl_bibliographies_versions_key);
	
	return $tables_auxiliaires;
}

?>