<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

	function allerdata_importe_rq_items(){
		include_spip('base/abstract_sql');
		// importer la table
		$importer_csv = charger_fonction('importer_csv','inc');
		$refs = $importer_csv(find_in_path('base/tbl_remarques_items.csv'),true);
		foreach($refs as $champs){
			$ins = array();
			$ins['remarques'] = $champs['remarques'];
			if (!sql_getfetsel('id_item','tbl_items','id_item='.intval($champs['id_item']))){
				echo "id_item ".$champs['id_item']." introuvable <br />";
			}
			else
				sql_updateq('tbl_items',$ins,'id_item='.intval($champs['id_item']));
		}
	}

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
			if (version_compare($current_version,'0.1.0.8','<')){
				allerdata_importe_rq_items();
				ecrire_meta($nom_meta_base_version,$current_version='0.1.0.8','non');
			}
			if (version_compare($current_version,'0.1.1.0','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items ADD nom_anglosaxon varchar(255) default NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.0','non');
			}
			if (version_compare($current_version,'0.1.1.1','<')){
				include_spip('base/abstract_sql');
				sql_alter("table tbl_items_versions ADD vu_id_auteur bigint(21) DEFAULT 0 NOT NULL");
				sql_alter("table tbl_items_versions ADD vu_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL");
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.1','non');
			}
			if (version_compare($current_version,'0.1.1.3','<')){
				include_spip('base/abstract_sql');
				if (lire_fichier(find_in_path("base/noms-anglais.txt"),$noms_anglais)){
					$noms_anglais = explode("\n",$noms_anglais);
					include_spip('base/abstract_sql');
					foreach($noms_anglais as $k=>$v){
						$v = explode("\t",trim($v));
						$id_item=intval($v[0]);
						$nom=trim($v[1]);
						sql_updateq("tbl_items", array('nom_anglosaxon'=>$nom),'id_item='.intval($id_item));
					}
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.1.3','non');
			}

			if (version_compare($current_version,'0.1.2.0','<')){
				include_spip('base/abstract_sql');
				include_spip('action/editer_tbl_item');
				$importer_csv = charger_fonction('importer_csv','inc');
				$ft = $importer_csv(find_in_path('base/familles_taxo_20100406.csv'),true);
				$ids = array();
				foreach($ft as $f){
					if ($row = sql_fetsel('id_item,nom,statut','tbl_items','id_item='.intval($f['id_item']))){
						if ($row['nom']!==$f['nom']) {
							echo $f['id_item']." : nom ".$row['nom']." => ".$f['nom']." <br />";
							tbl_items_set($f['id_item'], array('nom'=>$f['nom'],'statut'=>'publie'));
						}
					}
					else {
						echo "+".$f['id_item']. ' ' .$f['nom']." <br />";
						insert_tbl_item(2, $f['id_item']);
						tbl_items_set($f['id_item'], array('nom'=>$f['nom'],'statut'=>'publie'));
					}
					$ids[] = $f['id_item'];
				}
				$del = sql_allfetsel("id_item", "tbl_items", "id_type_item=2 AND ".sql_in('id_item',$ids,"NOT"));
				$del = array_map('reset',$del);
				include_spip('inc/allerdata_arbo');
				foreach ($del as $d){
					echo "Suppression $d<br />";
					$enfants = allerdata_les_enfants($d,'',true, true);
					foreach($enfants as $e) {
						$p = allerdata_les_parents($e, '', true, true);
						echo "$e>".implode(',',$p).' => ';
						$p = array_diff($p,array($d));
						echo "$e>".implode(',',$p) . '<br />';
						tbl_items_set($e, array('id_parent'=>$p));
					}
					tbl_items_set($d, array('statut'=>'poubelle'));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.2.0','non');
			}
			if (version_compare($current_version,'0.1.2.1','<')){
				include_spip('base/abstract_sql');
				include_spip('action/editer_tbl_item');
				$importer_csv = charger_fonction('importer_csv','inc');
				$ft = $importer_csv(find_in_path('base/sources_20100406.csv'),true);
				$ids = array();
				foreach($ft as $f){
					if ($row = sql_fetsel('id_item,nom,autre_nom,remarques,statut','tbl_items','id_item='.intval($f['id_item']))){
						if (
								 $row['nom']!=$f['nom']
							OR $row['autre_nom']!=$f['autre_nom']
							OR $row['remarques']!=$f['remarques_item']
							) {
							echo $f['id_item']." : nom ".$row['nom']." => ".$f['nom']." <br />";
							tbl_items_set($f['id_item'], array('nom'=>$f['nom'],'autre_nom'=>$f['autre_nom'],'remarques'=>$f['remarques_item'],'statut'=>'publie'));
						}
					}
					else {
						echo "+".$f['id_item']. ' ' .$f['nom']." <br />";
						insert_tbl_item(4, $f['id_item']);
						tbl_items_set($f['id_item'], array('nom'=>$f['nom'],'autre_nom'=>$f['autre_nom'],'remarques'=>$f['remarques_item'],'statut'=>'publie'));
					}
					$ids[] = $f['id_item'];
				}
				include_spip('inc/allerdata_arbo');
				$del = sql_allfetsel("id_item", "tbl_items", "id_type_item=4 AND ".sql_in('id_item',$ids,"NOT"));
				$del = array_map('reset',$del);
				foreach ($del as $d){
					echo "Suppression $d<br />";
					$enfants = allerdata_les_enfants($d,'',true, true);
					foreach($enfants as $e) {
						$p = allerdata_les_parents($e, '', true, true);
						echo "$e>".implode(',',$p).' => ';
						$p = array_diff($p,array($d));
						echo "$e>".implode(',',$p) . '<br />';
						tbl_items_set($e, array('id_parent'=>$p));
					}
					tbl_items_set($d, array('statut'=>'poubelle'));
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.2.1','non');
			}
			if (version_compare($current_version,'0.1.2.2','<')){
				include_spip('base/abstract_sql');
				include_spip('action/editer_tbl_item');
				$importer_csv = charger_fonction('importer_csv','inc');
				$liens = $importer_csv(find_in_path('base/parents_20100406.csv'),true);
				$parents = array();
				foreach($liens as $lien){
					$parents[$lien['id_item']][] = $lien['est_dans_id_item'];
				}
				$del = sql_allfetsel("id_item", "tbl_items", "id_type_item=4 AND ".sql_in('id_item',array_keys($parents),"NOT"));
				$del = array_map('reset',$del);
				foreach($del as $d){
					if (isset($parents[$d])) die('Erreur !');
					$parents[$d] = array(); // supprimer les parents pour ces items
				}
				include_spip('inc/allerdata_arbo');
				foreach($parents as $id => $ps){
					$po = allerdata_les_parents($id, '', true, true);
					if (count(array_diff($po,$ps)) OR count(array_diff($ps,$po))){
						echo "$id>".implode(',',$po)." => ";
						echo "$id>".implode(',',$ps)."<br />";
						tbl_items_set($id, array('id_parent'=>$ps));
					}
				}
				ecrire_meta($nom_meta_base_version,$current_version='0.1.2.2','non');
			}
			if (version_compare($current_version,'0.2.0','<')){
				include_spip('base/abstract_sql');
				include_spip('base/serial');
				include_spip('base/aux');

				sql_alter("TABLE tbl_items CHANGE nom nom_fr varchar(255) default NULL");
				sql_alter("TABLE tbl_items ADD nom_en varchar(255) default NULL AFTER nom_fr");
				sql_alter("TABLE tbl_items CHANGE autre_nom autre_nom_fr varchar(255) default NULL");
				sql_alter("TABLE tbl_items ADD autre_nom_en varchar(255) default NULL AFTER autre_nom_fr");
				sql_alter("TABLE tbl_items CHANGE nom_complet nom_complet_fr varchar(255) default NULL");
				sql_alter("TABLE tbl_items ADD nom_complet_en varchar(255) default NULL AFTER nom_complet_fr");
				sql_alter("TABLE tbl_items CHANGE nom_court nom_court_fr varchar(50) default NULL");
				sql_alter("TABLE tbl_items ADD nom_court_en varchar(50) default NULL AFTER nom_court_fr");
				sql_alter("TABLE tbl_items CHANGE chaine_alpha chaine_alpha_fr varchar(255) default NULL");
				sql_alter("TABLE tbl_items ADD chaine_alpha_en varchar(255) default NULL AFTER chaine_alpha_fr");
				sql_alter("TABLE tbl_items CHANGE representatif representatif_fr varchar(50) default NULL");
				sql_alter("TABLE tbl_items ADD representatif_en varchar(50) default NULL AFTER representatif_fr");
				sql_alter("TABLE tbl_items CHANGE fonction_classification fonction_classification_fr varchar(50) default NULL");
				sql_alter("TABLE tbl_items ADD fonction_classification_en varchar(50) default NULL AFTER fonction_classification_fr");

				sql_alter("TABLE tbl_items CHANGE source old_source varchar(100) NOT NULL");
				sql_alter("TABLE tbl_items CHANGE source_sans_accent old_source_sans_accent varchar(100) NOT NULL");
				sql_alter("TABLE tbl_items CHANGE famille old_famille varchar(100) NOT NULL");


				// Creer une vue par langue, ou les champs traduits sont projetes sur leur nom racine :
				// nom_en as nom, ...
				// avec fallback sur le champ _fr quand le champ traduit est vide
				$desc = $GLOBALS['tables_principales']['tbl_items'];
				$select = array_keys($desc['field']);
				include_spip('allerdata_fonctions');
				foreach(allerdata_langues() as $l){
					$sl = $select;
					$remove = array();
					// champs calcules en fonction de la langue
					foreach(array('nom','autre_nom','nom_complet','nom_court','chaine_alpha','representatif','fonction_classification') as $c){
						$sl[] = ($l=='fr')?"{$c}_fr AS {$c}":"IF(CHAR_LENGTH({$c}_{$l})>0,{$c}_{$l},{$c}_fr) AS {$c}";
						$remove = array_merge($remove,array_values(allerdata_liste_champs_trad($c)));
					}
					$sl = array_diff($sl,$remove);
					// la requete select
					$sl = sql_select($sl,'tbl_items','','','','','','',false);
					// creer la vue sur la langue
					$view = allerdata_vue('tbl_items',$l);
					// la supprimer d'abord, au cas ou
					sql_drop_view($view,'if exists');
					sql_create_view($view,$sl);
				}

				ecrire_meta($nom_meta_base_version,$current_version='0.2.0','non');
			}
			if (version_compare($current_version,'0.2.1','<')){
				$champs = array('nom_fr','autre_nom_fr','nom_complet_fr','nom_court_fr','chaine_alpha_fr','representatif_fr','fonction_classification_fr');
				$autoset = array();
				foreach($champs as $k)
					$autoset[preg_replace(',_fr$,','_en',$k)] = $k;
				// sur les familles taxo c'est facile !
				sql_update('tbl_items',$autoset,'id_type_item=2');
				// idem sur les organismes sources
				sql_update('tbl_items',$autoset,'id_type_item=4');
				echo "OK 0.2.1";
				ecrire_meta($nom_meta_base_version,$current_version='0.2.1','non');
			}
			if (version_compare($current_version,'0.2.2','<')){
				include_spip('inc/translate');
				$champs = array('nom_fr','autre_nom_fr','nom_complet_fr','nom_court_fr','chaine_alpha_fr','representatif_fr','fonction_classification_fr');
				$id_type_item=1;
				while ($id_type_item<13){

					$res = sql_select('id_item,nom_anglosaxon,'.implode(',',$champs),
														'tbl_items',
														'nom_en is null AND NOT nom_fr is null AND id_type_item='.intval($id_type_item),'','','0,80');
					while($row = sql_fetch($res)){
						$id_item = $row['id_item'];
						$nom_anglosaxon = ucfirst($row['nom_anglosaxon']);
						unset($row['id_item']);
						unset($row['nom_anglosaxon']);
						
						$set = array();
						foreach($row as $k=>$v)
							if (strlen($v))
								$set[preg_replace(',_fr$,','_en',$k)] = $v;
							else
								unset($row[$k]);

						$translate = $set;
						if ($nom_anglosaxon) {
							$set['nom_en'] = $nom_anglosaxon;
							if (isset($translate['nom_court_en']) AND $translate['nom_en']==$translate['nom_court_en']){
								$set['nom_court_en'] = $nom_anglosaxon;
								unset($translate['nom_court_en']);
							}
							unset($translate['nom_en']);
						}

						if (count($translate)){
							#$translate = translate($translate,'fr','en');
							#$set = array_merge($set,$translate);
						}

						sql_updateq('tbl_items',$set,'id_item='.intval($id_item));
						echo "#$id_item ";
					}
					$id_type_item++;
				}
				$n = sql_countsel('tbl_items',
														'nom_en is null AND NOT nom_fr is null AND id_type_item<13');
				echo "$n restant a traduire<br />";
				if ($n==0)
					ecrire_meta($nom_meta_base_version,$current_version='0.2.2','non');
			}
			if (version_compare($current_version,'0.3.0','<')){
				$file_csv = find_in_path("base/tbl_items_en.csv");
				$file_ser = _DIR_TMP.basename($file_csv,"csv")."-".md5($file_csv).".txt";
				if (!file_exists($file_ser)){
					$importer_csv = charger_fonction("importer_csv","inc");
					$items = $importer_csv($file_csv,true,";");
					ecrire_fichier($file_ser,serialize($items));
				}
				lire_fichier($file_ser,$data);
				if ($items = unserialize($data)
				  AND count($items)){
					$n = 0;
					$champs_trad = array('nom','autre_nom','nom_complet','chaine_alpha','representatif','fonction_classification','nom_court');
					echo count($items)." Items a importer<br />";
					while(count($items)
					  AND $item = array_shift($items)){

						$item = array_map("allerdata_clean_null",$item);
						$trads = array();
						foreach($champs_trad as $c){
							if ($v = $item[$c."_en"]){
								$trads[$c] = $v;
							}
						}
						if (count($trads)){
							$set = array();
							$row = sql_fetsel("*","tbl_items","id_item=".intval($item['id_item']));
							foreach($trads as $k=>$v){
								if ($row[$k."_fr"]!==$item[$k."_fr"]){
									var_dump($item);
									var_dump($row);
									die("Item a change !");
								}
								elseif($row[$k."_en"]!==$v) {
									$set[$k."_en"] = $v;
								}
							}
							if (count($set)){
								#var_dump($item);
								#var_dump($row);
								#var_dump($set);
								#die('MODIF ?');
								sql_updateq("tbl_items",$set,"id_item=".intval($item['id_item']));
								echo "Mise a jour item=".$item['id_item']." <br />";
							}
						}

						if ($n%10==0){
							ecrire_fichier($file_ser,serialize($items));
						}
					}
				}
				die('?');
				ecrire_meta($nom_meta_base_version,$current_version='0.3.0','non');
			}

		}
	}

	function allerdata_clean_null($v){
		return (($v==="NULL")?null:$v);
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