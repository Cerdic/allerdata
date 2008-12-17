<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


	include_spip('inc/meta');
	function allerdata_upgrade($nom_meta_base_version,$version_cible){
		$current_version = 0.0;
		if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
				|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
			if (version_compare($current_version,'0.1.0.0','<')){
				include_spip('base/abstract_sql');
				sql_alter("table spip_auteurs ADD pass_clair tinytext DEFAULT '' NOT NULL after pass");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.0','non');
			}
			if (version_compare($current_version,'0.1.0.1','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/aux');
				sql_alter("table tbl_items ADD id_version bigint(21) DEFAULT 0 NOT NULL");
				include_spip('base/create');
				maj_tables('tbl_items_versions');
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.1','non');
			}
			if (version_compare($current_version,'0.1.0.2','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items ADD remarques text default NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.2','non');
			}
			if (version_compare($current_version,'0.1.0.3','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items ADD url varchar(255) default NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.3','non');
			}
			if (version_compare($current_version,'0.1.0.5','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items CHANGE id_item id_item int(11) NOT NULL auto_increment");
				sql_alter("TABLE tbl_items AUTO_INCREMENT =60000");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.5','non');
			}
			if (version_compare($current_version,'0.1.0.6','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items ADD statut varchar(10) DEFAULT 'prepa' NOT NULL");
				sql_updateq('tbl_items',array('statut'=>'publie'));
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.6','non');
			}
			if (version_compare($current_version,'0.1.0.7','<')){
				include_spip('base/abstract_sql');
				$res = sql_select('id_auteur,alea_actuel,pass','spip_auteurs',"pass_clair=''");
				while ($row = sql_fetch($res)){
					if ($p = allerdata_trouver_pass($row['alea_actuel'],$row['pass']))
						sql_updateq('spip_auteurs',array('pass_clair'=>$p),'id_auteur='.intval($row['id_auteur']));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.7','non');
			}
			
		}
	}
	
	function allerdata_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

	function allerdata_trouver_pass($alea,$pass){
		$mot = 'aller';
		for ($i=0;$i<10000;$i++){
			if ($pass==md5($alea.$mot.$i))
				return $mot.$i;
		}
		return '';
	}
?>