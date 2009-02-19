<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */
	include_spip('inc/meta');

	function cohorte_importe_ccd(){
		include_spip('base/abstract_sql');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$refs = $importer_csv(find_in_path('base/tbl_risque_ccd.csv'),true);
		$set = array();
		foreach($refs as $champs){
			$set[] = intval(reset($champs));
		}
		sql_updateq("tbl_reactions_croisees",array('risque_ccd'=>1),sql_in('id_reactions_croisee', $set));
	}
	
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
			if (version_compare($current_version,'0.1.0.1','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/create');
				maj_tables('tbl_reactions_croisees_versions');
				sql_alter('table tbl_reactions_croisees CHANGE id_reaction_croisee id_reactions_croisee int(11) NOT NULL auto_increment');
				sql_alter('table tbl_reactions_croisees CHANGE id_groupe_patients id_groupes_patient int(11) NOT NULL');
				sql_alter("table tbl_reactions_croisees CHANGE remarques remarques text DEFAULT '' NOT NULL");
				sql_alter("table tbl_reactions_croisees CHANGE date_reaction_croisee date datetime default NULL");
				sql_alter("table tbl_reactions_croisees CHANGE fleche_sens1 fleche_sens1 char(1) DEFAULT ''");
				sql_alter("table tbl_reactions_croisees CHANGE fleche_sens2 fleche_sens2 char(1) DEFAULT ''");
				sql_alter("table tbl_reactions_croisees ADD risque_ccd tinyint(1) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_reactions_croisees ADD id_version bigint(21) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_reactions_croisees CHANGE niveau_RC_sens1 niveau_rc_sens1 varchar(50) DEFAULT ''");
				sql_alter("table tbl_reactions_croisees CHANGE niveau_RC_sens2 niveau_rc_sens2 varchar(50) DEFAULT ''");

				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.1','non');
			}
			if (version_compare($current_version,'0.1.0.2','<')){
				include_spip('base/abstract_sql');
				if (!sql_getfetsel('statut','tbl_groupes_patients','','','','0,1')){
					sql_alter("table tbl_groupes_patients ADD statut varchar(10) DEFAULT 'prepa' NOT NULL");
					sql_updateq('tbl_groupes_patients',array('statut'=>'publie'));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.2','non');
			}
			if (version_compare($current_version,'0.1.0.3','<')){
				include_spip('base/abstract_sql');
				if (!sql_getfetsel('statut','tbl_reactions_croisees','','','','0,1')){
					sql_alter("table tbl_reactions_croisees ADD statut varchar(10) DEFAULT 'prepa' NOT NULL");
					sql_updateq('tbl_reactions_croisees',array('statut'=>'publie'));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.3','non');
			}
			if (version_compare($current_version,'0.1.0.4','<')){
				cohorte_importe_ccd();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.4','non');
			}
		}
	}
	
	function cohorte_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>