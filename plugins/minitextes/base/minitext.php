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
		if (version_compare($current_version,'0.1.0.4','<')){
			include_spip('base/abstract_sql');
			include_spip('base/serial');
			include_spip('base/aux');
			include_spip('base/create');

			maj_tables('tbl_minitextes');
			maj_tables('tbl_minitextes_items');
			maj_tables('tbl_items');
			maj_tables('tbl_minitextes_versions');

			ecrire_meta($nom_meta_base_version,$current_version='0.1.0.4','non');
		}
		if (version_compare($current_version,'0.1.1.0','<')){
			$importer_csv = charger_fonction('importer_csv','inc');
			$mts = $importer_csv(find_in_path('base/mt_import.csv'),true);
			include_spip('action/editer_tbl_minitexte');
			foreach($mts as $mt){
				$id = $mt['id_mini_texte'];
				if (!sql_getfetsel('id_minitexte', 'tbl_minitextes', 'id_minitexte='.intval($id)))
					$id = insert_tbl_minitexte($id);
				if ($id){
					$set = array(
						'type' => ($mt['type_mini_texte'] == 'P')?1:(($mt['type_mini_texte'] == 'RC')?2:3),
						'texte' =>
						  "{{{".$mt['titre_mini_texte']."}}}\n"
						  .$mt['Intro']."\n\n"
						  .(strlen($s=$mt['Allerg_principaux'])?"{{Allergènes principaux}}\n_ $s\n\n":"")
						  .(strlen($s=$mt['Diagn_molec'])?"{{Diagnostic moléculaire}}\n_ $s\n\n":"")
						  .(strlen($s=$mt['Allerg_representatifs'])?"{{Allergènes représentatifs}}\n_ $s\n\n":"")
						);
					tbl_minitextes_set($id,$set);
				}
			}

			$liens = $importer_csv(find_in_path('base/mt_import_liens.csv'),true);
			$links = array();
			while (count($liens)){
				$z = array_shift($liens);
				$links[$z['id_mini_texte']][] = $z['id_item'];
			}
			foreach($links as $id => $items){
				$type = sql_getfetsel("type", "tbl_minitextes", "id_minitexte=".intval($id));
				$set = array();
				switch ($type){
					case 1:
						$set['id_items'] = $items;
						$set['statut'] = 'publie';
						break;
					case 2:
						if (count($items)!=2) {
							var_dump($items);
							die("Erreur minitexte $id, type 2");
						}
						$set['id_item_1'] = array_shift($items);
						$set['id_item_2'] = array_shift($items);
						$set['statut'] = 'publie';
						break;
					case 3:
						if (count($items)!=1){
							var_dump($items);
							die("Erreur minitexte $id, type 3");
						}
						$set['id_item'] = array_shift($items);
						$set['statut'] = 'publie';
						break;
				}
				tbl_minitextes_set($id,$set);
			}
			ecrire_meta($nom_meta_base_version,$current_version='0.1.1.0','non');
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
	  'type' => "tinyint(1) NOT NULL",
		"texte"	=> "longtext DEFAULT '' NOT NULL",
		#'incidence_rav' => "bigint(21) NOT NULL",
		"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
		'date' => "datetime default NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
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

	$tbl_minitextes_versions = array(
	  'id_minitexte' => "int(11) NOT NULL",
		"id_version"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"id_auteur"	=> "bigint(21) NOT NULL",
		"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"commentaires" => "text",
		"diff" => "text", // liste des champs modifies avant/apres
		"vu_id_auteur"	=> "bigint(21) DEFAULT 0 NOT NULL",
		"vu_date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
  );
	$tbl_minitextes_versions_key = array (
			"PRIMARY KEY"	=> "id_minitexte, id_version");
	$tables_auxiliaires['tbl_minitextes_versions'] = array(
		'field' => &$tbl_minitextes_versions,
		'key' => &$tbl_minitextes_versions_key);
	
	return $tables_auxiliaires;
}

?>