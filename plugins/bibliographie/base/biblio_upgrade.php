<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

	include_spip('inc/meta');
	
	
	function biblio_upgrade($nom_meta_base_version,$version_cible){
		$current_version = 0.0;
		if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
				|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
			if (version_compare($current_version,'0.1.0.0','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/create');
				maj_tables('tbl_journals');
				// importer la table
				$importer_csv = charger_fonction('importer_csv','inc');
				$journaux = $importer_csv(find_in_path('base/tbl_journaux.csv'));
				$ins = array();
				foreach($journaux as $champs)
					$ins[] = array('nom'=>reset($champs));
				sql_insertq_multi('tbl_journals',$ins);
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.0','non');
			}
		}
	}
	
	function biblio_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>