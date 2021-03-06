<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


function cohorte_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	$interface['table_des_tables']['groupes_patients']='tbl_groupes_patients';

	// jointures
	$interface['tables_jointures']['tbl_groupes_patients'][]='tbl_reactions_croisees';
	$interface['tables_jointures']['tbl_reactions_croisees'][]='tbl_groupes_patients';
	$interface['table_des_traitements']['DESCRIPTION']['tbl_groupes_patients']=  _TRAITEMENT_TYPO;
	$interface['table_des_traitements']['REMARQUES']['tbl_groupes_patients']=  _TRAITEMENT_TYPO;
	$interface['table_des_traitements']['PAYS']['tbl_groupes_patients']= "traduit_pays(%s)";

	return $interface;
}

function cohorte_declarer_tables_principales($tables_principales){
  
	$groupes_patients = array(
		'id_groupes_patient' => "int(11) NOT NULL",
		'nom'	=> "text DEFAULT '' NOT NULL",
		'id_bibliographie' => "int(11) NOT NULL",
		'description' => "text DEFAULT '' NOT NULL",
		'date' => "datetime default NULL",
		'nb_sujets'=>"varchar(50) DEFAULT ''",
		'pool' => "tinyint(1) DEFAULT 0 NOT NULL",
		'qualitatif' => "tinyint(1) DEFAULT 0 NOT NULL",
		'inexploitable' => "tinyint(1) DEFAULT 0 NOT NULL",
		'pays'=>"varchar(50) DEFAULT ''",
		'remarques' => "text DEFAULT '' NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
	);
	
	$groupes_patients_key = array(
			"PRIMARY KEY"	=> "id_groupes_patient",
			"INDEX"	=> "id_bibliographie",
	);
	
	$tables_principales['tbl_groupes_patients'] =
		array('field' => &$groupes_patients, 'key' => &$groupes_patients_key);

	$reactions_croisees = array(
		'id_reactions_croisee' => "int(11) NOT NULL",
		'id_groupes_patient' => "int(11) NOT NULL",
		'id_produit1' => "int(11) NOT NULL",
		'molecules1'=>"varchar(50) DEFAULT ''",
		'niveau_rc_sens1'=>"varchar(50) DEFAULT ''",
		'niveau_rc_sens2'=>"varchar(50) DEFAULT ''",
		'id_produit2' => "int(11) NOT NULL",
		'molecules2'=>"varchar(50) DEFAULT ''",
		'remarques' => "text DEFAULT '' NOT NULL",
		'date' => "datetime default NULL",
		'fleche_sens1' => "char(1) DEFAULT ''",
		'fleche_sens2' => "char(1) DEFAULT ''",
		'produits_differents' => "tinyint(1) DEFAULT 0 NOT NULL",
		'risque_ccd' =>"tinyint(1) DEFAULT 0 NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
	);
	
	$reactions_croisees_key = array(
			"PRIMARY KEY"	=> "id_reactions_croisee",
			"INDEX"	=> "id_groupes_patient",
	);
	
	$tables_principales['tbl_reactions_croisees'] =
		array('field' => &$reactions_croisees, 'key' => &$reactions_croisees_key);
	return $tables_principales;
}

function cohorte_declarer_tables_auxiliaires($tables_auxiliaires){
		
	$groupes_patients_versions = array(
	  'id_groupes_patient' => "int(11) NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"commentaires" => "text",
		"diff" => "text", // liste des champs modifies avant/apres
		"vu_id_auteur"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"vu_date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
  );
	$groupes_patients_versions_key = array (
			"PRIMARY KEY"	=> "id_groupes_patient, id_version");
	$tables_auxiliaires['tbl_groupes_patients_versions'] = array(
		'field' => &$groupes_patients_versions,
		'key' => &$groupes_patients_versions_key);

	$reactions_croisees_versions = array(
	  'id_reactions_croisee' => "int(11) NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"commentaires" => "text",
		"diff" => "text", // liste des champs modifies avant/apres
		"vu_id_auteur"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"vu_date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
  );
	$reactions_croisees_versions_key = array (
			"PRIMARY KEY"	=> "id_reactions_croisee, id_version");
	$tables_auxiliaires['tbl_reactions_croisees_versions'] = array(
		'field' => &$reactions_croisees_versions,
		'key' => &$reactions_croisees_versions_key);

	return $tables_auxiliaires;
}

?>