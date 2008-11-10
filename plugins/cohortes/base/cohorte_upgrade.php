<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */
	include_spip('inc/meta');
	
	
	function cohorte_upgrade($nom_meta_base_version,$version_cible){
		$current_version = 0.0;
		if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
				|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
			if (version_compare($current_version,'0.1.0.0','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/create');
				maj_tables('tbl_groupes_patients_versions');
				sql_alter('table tbl_groupes_patients CHANGE id_groupe_patients id_groupes_patient int(11) NOT NULL auto_increment');
				sql_alter("table tbl_groupes_patients CHANGE nom_groupe_patients nom text DEFAULT '' NOT NULL");
				sql_alter("table tbl_groupes_patients CHANGE description_groupe description text DEFAULT '' NOT NULL");
				sql_alter("table tbl_groupes_patients CHANGE date_groupe_patients date datetime default NULL");
				sql_alter("table tbl_groupes_patients ADD remarques text DEFAULT '' NOT NULL");
				sql_alter("table tbl_groupes_patients ADD inexploitable tinyint(1) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_groupes_patients ADD id_version bigint(21) DEFAULT 0 NOT NULL");

				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.0','non');
			}

		}
	}
	
	function cohorte_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>