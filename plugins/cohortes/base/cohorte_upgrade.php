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

	function cohorte_importe_cohortes(){
		include_spip('base/abstract_sql');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$cohortes = $importer_csv(find_in_path('base/tbl_groupes_patients.csv'),true);
		echo "Nombre de groupes a importer :".count($cohortes)."<br />";

		// la table remplace l'existant, mais il ne faut pas perdre les cle primaires
		// on commence par verifier si il y a des cohortes a virer
		$noms = array_map('reset',$cohortes);

		$not = sql_allfetsel("id_groupes_patient", "tbl_groupes_patients", "statut!='poubelle' AND ".sql_in('nom',$noms,"NOT"));
		$not = array_map('reset',$not);
		if (count($not)){
			echo count($not). " groupes de patient vont etre supprimes<br />";
			foreach($not as $id_groupes_patient){
				echo "Suppression du groupe de patients $id_groupes_patient<br />";
				$rc = sql_allfetsel("id_reactions_croisee", "tbl_reactions_croisees", "statut!='poubelle' AND ".'id_groupes_patient='.intval($id_groupes_patient));
				if (count($rc)){
					$rc = array_map('reset',$rc);
					echo "Suppression des RC associees :".implode(', ',$rc)."<br />";
					sql_updateq("tbl_reactions_croisees", array('statut'=>'poubelle'),sql_in('id_reactions_croisee',$rc));
				}
				sql_updateq("tbl_groupes_patients", array('statut'=>'poubelle'),'id_groupes_patient='.intval($id_groupes_patient));
			}
		}

		include_spip('action/editer_tbl_groupes_patient');

		$update = $crea = 0;
		foreach($cohortes as $values){
			#var_dump($values);
			$set = array(
				'nom' => $values['groupe de patient'],
 				'description' => $values['recrutement'],
				'date' => date('Y-m-d H:i:s',strtotime($values['date'])),
				'nb_sujets' => $values['nombre de sujets'],
				'pool' => $values['serums testes separement']?0:1,
				'qualitatif' => $values['inhibitions chiffrees']?0:1,
				'inexploitable' => $values['pas de RC testee etc']?1:0,
				'pays' => $values['pays'],
				'remarques' => $values['remarques'],
				'statut' => 'publie',
			);
			if (!($id_groupes_patient = sql_getfetsel('id_groupes_patient', 'tbl_groupes_patients', 'nom='.sql_quote($set[nom]), $groupby, $orderby))){
				list($id_bibliographie,$n) = explode('-',$set['nom']);
				echo "Creation d'un groupe pour $id_bibliographie.<br />";
				#var_dump($set);
				$crea++;
				$id_groupes_patient = insert_tbl_groupes_patient($id_bibliographie);
			}
			else
				$update++;
			tbl_groupes_patients_set($id_groupes_patient, $set);
		}
		$in = sql_allfetsel("id_groupes_patient", "tbl_groupes_patients", sql_in('nom',$noms));
		echo "Nombre de groupes crees :$crea<br />";
		echo "Nombre de groupes maj :$update<br />";
		echo "Nombre de groupes en base :".sql_countsel('tbl_groupes_patients')."<br />";
	}

	function cohorte_importetraden(){
		$importer_csv = charger_fonction('importer_csv','inc');
		$trads = $importer_csv(find_in_path('base/cohortestrad_en.csv'),true);

		foreach($trads as $t){
			$id_groupes_patient = $t['ID_GROUPES_PATIENT'];
			$set = array();
			if (strlen($t['DESCRIPTION_FR']) OR strlen($t['DESCRIPTION_EN'])){
				$set['description']="<multi>[fr]".$t['DESCRIPTION_FR']."[en]".$t['DESCRIPTION_EN']."</multi>";
			}
			if (strlen($t['REMARQUES_FR']) OR strlen($t['REMARQUES_EN'])){
				$set['remarques']="<multi>[fr]".$t['REMARQUES_FR']."[en]".$t['REMARQUES_EN']."</multi>";
			}
			if (count($set)){
				sql_updateq('tbl_groupes_patients',$set,'id_groupes_patient='.intval($id_groupes_patient));
			}
		}
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
			if (version_compare($current_version,'0.1.0.5','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_groupes_patients_versions ADD vu_id_auteur bigint(21) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_groupes_patients_versions ADD vu_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL");
				sql_alter("table tbl_reactions_croisees_versions ADD vu_id_auteur bigint(21) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_reactions_croisees_versions ADD vu_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.5','non');
			}
			if (version_compare($current_version,'0.1.0.6','<')){
				cohorte_importe_cohortes();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.6','non');
			}
			if (version_compare($current_version,'0.1.1.0','<')){
				cohorte_importetraden();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.0','non');
			}

		}
	}
	
	function cohorte_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>