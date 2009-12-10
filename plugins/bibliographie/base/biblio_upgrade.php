<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

	include_spip('inc/meta');

	function biblio_nettoie_doublons_journaux(){
		$journaux = array_map('reset',sql_allfetsel("id_journal","tbl_journals","nom='Rev Esp Alergol Immunol Clin'"));
		if (count($journaux)>1){
			if ($id_journal = intval(array_shift($journaux))){
				foreach($journaux as $k=>$v){
					$journaux[$k] = intval($v);
					if (!$journaux[$k])
						unset($journaux[$k]);
				}
				sql_updateq('tbl_bibliographies',array('id_journal'=>$id_journal),sql_in("id_journal",$journaux));
				sql_delete('tbl_journals', sql_in("id_journal",$journaux));
			}
		}
	}
	
	function biblio_importe_references(){
		biblio_nettoie_doublons_journaux();
		include_spip('inc/biblio');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$refs = $importer_csv(find_in_path('base/tbl_bibliographies_2.csv'),true);
		foreach($refs as $champs){
			$ins = array();
			$ins['id_bibliographie'] = $champs['id_biblio'];
			foreach(array('auteurs','titre','annee','volume','supplement','numero','autre_media','abstract') as $c)
				$ins[$c] = $champs[$c];
			
			if (strlen($champs['journal'])){
				// trouver le journal
				$journaux = biblio_rechercher_journal($champs['journal']);
				if (count($journaux)==0){
					$ins['id_journal'] = sql_insertq('tbl_journals',array('nom'=>$champs['journal']));
					echo "journal ". $ins['id_journal']. '/' .$champs['journal']. ' ajoute<br />';
				}
				elseif (count($journaux)!=1){
					echo 'journal '.$champs['journal'].'introuvable ou ambigu :'.var_export(count($journaux),true);
					var_dump($champs);
					var_dump($journaux);
					die();
				}
				else {
					$journaux = array_keys($journaux);
					$ins['id_journal'] = reset($journaux);
				}
			}
			$ins['premiere_page'] = $champs['prem_page'];
			$ins['derniere_page'] = $champs['dern_page'];
			if (isset($champs['lien_ressource_web']))
				$ins['url'] = $champs['lien_ressource_web'];
			$ins['full_text_disponible'] = $champs['full_text_disponible']=='VRAI'?1:0;
			if ($champs['date_biblio'])
				$ins['date'] = date('Y-m-d H:i:s',strtotime($champs['date_biblio']));
			if (sql_getfetsel('id_bibliographie','tbl_bibliographies','id_bibliographie='.intval($ins['id_bibliographie'])))
				sql_updateq('tbl_bibliographies',$ins,'id_bibliographie='.intval($ins['id_bibliographie']));
			else
				sql_insertq('tbl_bibliographies',$ins);
		}
		
	}
	
	function biblio_importe_notes(){
		include_spip('inc/biblio');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$refs = $importer_csv(find_in_path('base/tbl_notes_biblio.csv'),true);
		foreach($refs as $champs){
			$ins = array();
			$ins['id_biblio_note'] = $champs['id_notes_biblio'];
			$ins['id_bibliographie'] = $champs['id_biblio'];
			$ins['id_auteur'] = 14; // import : tout vient de HM
			$ins['texte'] = $champs['notes_biblio'];
			$ins['date'] = date('Y-m-d H:i:s',strtotime($champs['date_notes_biblio']));
			if (sql_getfetsel('id_biblio_note','tbl_biblio_notes','id_biblio_note='.intval($ins['id_biblio_note'])))
				sql_updateq('tbl_biblio_notes',$ins,'id_biblio_note='.intval($ins['id_biblio_note']));
			else
				sql_insertq('tbl_biblio_notes',$ins);
		}		
	}

	function biblio_importe_abstracts(){
		if (lire_fichier(find_in_path('base/abstracts.txt'),$abstracts)){
			$abstracts = explode("\n",$abstracts);
			foreach($abstracts as $a){
				if (preg_match(',^([0-9]+)[\s]+,',$a,$regs)){
					$id_biblio = intval($regs[1]);
					$a = substr($a,strlen($regs[0]));
					if (strpos($a,"£££")!==FALSE){
						$a = str_replace("£££","\n\n",$a);
						#echo "$id_biblio:$a";
						#die();
					}
					sql_updateq('tbl_bibliographies',array('abstract'=>$a),'id_bibliographie='.intval($id_biblio));
				}
				else {
					echo "Erreur (ligne incomprehensible) : ".$a;
					die();
				}
			}
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
					if (!sql_countsel('tbl_journals','nom='.sql_quote(reset($champs))))
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
				sql_alter("table tbl_bibliographies DROP INDEX `n� article`");
				// mettre a jour le reste de la table
				maj_tables('tbl_bibliographies');
				#biblio_importe_references();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.1','non');
			}
			if (version_compare($current_version,'0.1.0.2','<')){
				include_spip('base/abstract_sql');
				include_spip('base/aux');
				include_spip('base/create');
				maj_tables('spip_bibliographies_articles');
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.2','non');
			}
			if (version_compare($current_version,'0.1.0.3','<')){
				include_spip('base/abstract_sql');
				// un oubli !
				sql_alter('table tbl_groupes_patients CHANGE id_biblio id_bibliographie int(11) NOT NULL');

				// les notes biblios
				include_spip('base/serial');
				include_spip('base/create');
				maj_tables('tbl_biblio_notes');
				biblio_importe_notes();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.3','non');
			}
			if (version_compare($current_version,'0.1.0.4','<')){
				include_spip('base/abstract_sql');
				include_spip('inc/biblio');
				sql_update('tbl_bibliographies',array('url'=>"concat('"._URL_PUBMED."',url)"),"url REGEXP '^[0-9]+$'");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.4','non');
			}
			if (version_compare($current_version,'0.1.0.5','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_bibliographies ADD doublons_refs VARCHAR(255) DEFAULT '' NOT NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.5','non');
			}
			if (version_compare($current_version,'0.1.0.6','<')){
				include_spip('base/abstract_sql');
				include_spip('base/aux');
				include_spip('base/create');
				maj_tables('tbl_bibliographies_versions');
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.6','non');
			}
			if (version_compare($current_version,'0.1.0.7','<')){
				include_spip('base/abstract_sql');
				include_spip('base/aux');
				// on refait les liens !
				sql_delete('spip_bibliographies_articles');
				include_spip('inc/biblio');
				$res = sql_select('id_article,texte,chapo','spip_articles');
				while ($row = sql_fetch($res)){
					marquer_liens_biblios($row,$row['id_article'],'article','id_article','articles','spip_articles');
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.7','non');
			}
			if (version_compare($current_version,'0.1.0.8','<')){
				include_spip('base/abstract_sql');
				if (!sql_getfetsel('statut','tbl_bibliographies','','','','0,1')){
					sql_alter("table tbl_bibliographies ADD statut varchar(10) DEFAULT 'prepa' NOT NULL");
					sql_updateq('tbl_bibliographies',array('statut'=>'publie'));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.8','non');
			}
			if (version_compare($current_version,'0.1.0.9','<')){
				include_spip('base/abstract_sql');
				biblio_importe_references();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.9','non');
			}
			if (version_compare($current_version,'0.1.1.0','<')){
				include_spip('base/abstract_sql');
				biblio_importe_abstracts();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.0','non');
			}
			if (version_compare($current_version,'0.1.1.1','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_bibliographies_versions ADD vu_id_auteur bigint(21) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_bibliographies_versions ADD vu_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.1','non');
			}

		}
	}
	
	function biblio_vider_tables($nom_meta_base_version) {
		effacer_meta($nom_meta_base_version);
	}

?>