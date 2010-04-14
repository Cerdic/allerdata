<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

include_spip('base/abstract_sql');

/**
 * Recuperer les enfants d'un item
 * - eventuellement d'un type donne
 * - directement contenus ou non
 * - publie ou en attente
 *
 * @param int $id_item
 * @param string $type_item
 * @param bool $direct
 * @param bool $tous
 * @return array
 */

function allerdata_les_enfants($id_item,$type_item='',$direct=true,$tous=true){
	include_spip('allerdata_fonctions');
	$where = is_array($id_item)?sql_in("ed.est_dans_id_item",$id_item):'ed.est_dans_id_item='.intval($id_item);
	return array_map('reset',sql_allfetsel('i.id_item','tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_item',
	$where
	.($direct?" AND ed.directement_contenu=1":"")
	.($type_item?' AND '.sql_in('i.id_type_item',allerdata_id_type_item($type_item,$tous)):"")
	));
}


/**
 * Recuperer les parents d'un item
 * - eventuellement d'un type donne
 * - directement contenant ou non
 * - publie ou tous
 *
 * @param int $id_item
 * @param string $type_item
 * @param bool $direct
 * @param bool $tous
 * @return array
 */
function allerdata_les_parents($id_item,$type_item='',$direct=true,$tous=true){
	include_spip('allerdata_fonctions');
	$where = is_array($id_item)?sql_in("ed.id_item",$id_item):'ed.id_item='.intval($id_item);
	return array_map('reset',sql_allfetsel('i.id_item','tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.est_dans_id_item',
	$where
	.($direct?" AND ed.directement_contenu=1":"")
	.($type_item?' AND '.sql_in('i.id_type_item',allerdata_id_type_item($type_item,$tous)):"")
	));
}

function allerdata_modifier_les_parents($id_item,$parents){
	if (!is_array($parents)) $parents = array($parents);
	$anciens = allerdata_les_parents($id_item);
	// enlever les anciens qui ne sont plus la
	$remove = array_diff($anciens,$parents);
	foreach ($remove as $id_parent)
		allerdata_remove_filiation($id_item,$id_parent);
	// ajouter les nouveaux
	$add = array_unique(array_diff($parents,$anciens));
	foreach ($add as $id_parent)
		allerdata_create_filiation($id_item,$id_parent);
}

/**
 * Affilier un ou des enfants a un parent en direct ou non, 
 * et propager a tous ses ascendants
 *
 * @param int/array $id_items
 * @param int $id_parent
 * @param bool $direct
 */
function allerdata_create_filiation($id_items, $id_parent, $direct = true, $propage = true) {
	
	// regarder si la filiation existe deja ou non
	$les_enfants = allerdata_les_enfants($id_parent,'',$direct);
	// accepter un appel avec un seul item
	$id_items = is_array($id_items)?$id_items:array($id_items);

	// prendre les items qui ne sont pas deja listes dans les enfants
	$id_items = array_diff($id_items,$les_enfants);
	if (count($id_items)){
		foreach($id_items as $id_item){
			$insert[] = array('id_item'=>$id_item,'est_dans_id_item'=>$id_parent,'date_est_dans'=>'NOW()','directement_contenu'=>$direct?1:0);
		}
		spip_log("+ items ".implode(',',$id_items)." affilies a l'item $id_parent".($direct ? " en direct":""),'allerdata_arbo');
		sql_insertq_multi('tbl_est_dans',$insert);

		if ($propage){
			include_spip('allerdata_fonctions');
			$racines = allerdata_item_racines($id_parent);
			spip_log("  ! racines de $id_parent : ".implode(',',$racines),'allerdata_arbo');
			foreach($racines as $racine)
				allerdata_verifier_filiations($racine);
		}
	}
}


/**
 * De-Affilier un ou des enfants a un parent, 
 * et propager a tous ses ascendants
 *
 * @param int/array $id_items
 * @param int $id_parent
 * @param bool $direct
 */
function allerdata_remove_filiation($id_items, $id_parent, $propage = true) {
	
	// regarder si la filiation existe bien
	$les_enfants = allerdata_les_enfants($id_parent,'',false);
	// accepter un appel avec un seul item
	$id_items = is_array($id_items)?$id_items:array($id_items);

	// prendre les items qui sont bien listes dans les enfants
	$id_items = array_intersect($id_items,$les_enfants);
	if (count($id_items)){
		spip_log("X items ".implode(',',$id_items)." enleves de l'item $id_parent",'allerdata_arbo');
		sql_delete('tbl_est_dans',"est_dans_id_item=".$id_parent." AND ".sql_in("id_item",$id_items));
		if ($propage){
			include_spip('allerdata_fonctions');
			$racines = allerdata_item_racines($id_parent);
			spip_log("  ! racines de $id_parent : ".implode(',',$racines),'allerdata_arbo');
			foreach($racines as $racine)
				allerdata_verifier_filiations($racine);
		}
	}
}

function allerdata_verifier_filiations($id_item){
	$enfants = array();
	// verifier la filiation de tous les enfants directs
	$enfants_directs = allerdata_les_enfants($id_item);
	// verifier qu'il n'y a pas un lien sur lui meme avec directement_contenu=1
	if (in_array($id_item,$enfants_directs))
		allerdata_remove_filiation($id_item,$id_item,false);
	foreach($enfants_directs as $id_enfant){
		if ($id_enfant!=$id_item) // precaution
			$enfants = array_merge($enfants,allerdata_verifier_filiations($id_enfant));
	}
	// ajouter lui meme dans la liste des enfants indirect pour creer l'auto lien
	$enfants = array_merge($enfants,array($id_item));
	// creer les liens manquants eventuels
	allerdata_create_filiation($enfants, $id_item, false, false);
	// completer avec les enfants directs
	$enfants = array_merge($enfants,$enfants_directs);
	
	// verifier que l'info en base correspond bien
	$les_enfants = allerdata_les_enfants($id_item,'',false);
	// enlever les eventuels sur numeraires errones
	allerdata_remove_filiation(array_diff($les_enfants,$enfants),$id_item,false);

	#SELECT est_dans_id_item,count(est_dans_id_item) as total FROM `tbl_est_dans` WHERE id_item=163 group by est_dans_id_item having total>1

	// renvoyer tous les enfants indirects de cet item pour que le parent se les recuperes
	return $enfants;
}

function allerdata_verifier_arbre(){
	// recuperer tous les items racine (sans parent autre qu'eux meme)
	$racines = sql_select("id_item","tbl_items","id_item NOT IN (".sql_get_select('DISTINCT zzz.id_item','tbl_est_dans AS zzz','zzz.id_item<>zzz.est_dans_id_item').")");
	while ($row = sql_fetch($racines))
		allerdata_verifier_filiations($row['id_item']);
}

?>