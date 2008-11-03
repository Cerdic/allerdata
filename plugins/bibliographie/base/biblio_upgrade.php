<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

	include_spip('inc/meta');
	
	function biblio_importe_references(){
		include_spip('inc/biblio');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$refs = $importer_csv(find_in_path('base/tbl_bibliographies.csv'),true);
		foreach($refs as $champs){
			$ins = array();
			$ins['id_bibliographie'] = $champs['id_biblio'];
			foreach(array('auteurs','titre','annee','volume','supplement','numero','autre_media','abstract') as $c)
				$ins[$c] = $champs[$c];
			
			if (strlen($champs['journal'])){
				$journaux = biblio_rechercher_journal($champs['journal']);
				if (count($journaux)!=1){
					echo 'journal '.$champs['journal'].'introuvable ou ambigu :'.var_export(count($journaux),true);
					var_dump($champs);
					die();
				}
			}
			// trouver le journal
			$journaux = array_keys($journaux);
			$ins['id_journal'] = reset($journaux);
			$ins['premiere_page'] = $champs['prem_page'];
			$ins['derniere_page'] = $champs['dern_page'];
			$ins['url'] = $champs['lien_ressource_web'];
			$ins['full_text_disponible'] = $champs['full_text_disponible']=='VRAI'?1:0;
			$ins['date'] = date('Y-m-d H:i:s',strtotime($champs['date_biblio']));
			if (sql_getfetsel('id_bibliographie','tbl_bibliographies','id_bibliographie='.intval($ins['id_bibliographie'])))
				sql_updateq('tbl_bibliographies',$ins,'id_bibliographie='.intval($ins['id_bibliographie']));
			else
				sql_insertq('tbl_bibliographies',$ins);
		}
		
	}
	
	
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
			if (version_compare($current_version,'0.1.0.1','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/create');
				// changer le nom de la cle primaire
				sql_alter('table tbl_bibliographies CHANGE id_biblio id_bibliographie int(11) NOT NULL auto_increment');
				// le type du champ citation
				sql_alter("table tbl_bibliographies CHANGE citation citation text DEFAULT '' NOT NULL");
				// virer un index bien inutile
				sql_alter("table tbl_bibliographies DROP INDEX `n¡ article`");
				// mettre a jour le reste de la table
				maj_tables('tbl_bibliographies');
				biblio_importe_references();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.1','non');
			}
		}
	}
	
	function biblio_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>