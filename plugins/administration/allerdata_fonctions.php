<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

include_spip('base/serial');
$GLOBALS['tables_principales']['spip_auteurs']['pass_clair']="tinytext DEFAULT '' NOT NULL";

/**
 * Regarder si un item a des enfants designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les enfants presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_sans_enfant($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_est_dans','ed.id_item='.intval($id_item));
}
/**
 * Regarder si un item a des parents designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les parents presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_orphelin($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_item','ed.id_est_dans='.intval($id_item));
}

/**
 * Enter description here...
 *
 * @param unknown_type $id_type_item
 * @param unknown_type $plur
 * @return unknown
 */
function allerdata_type_item($id_type_item,$plur=''){
	switch ($id_type_item){
		case 2:
			return 'famille_taxo'.$plur;
			break;
		case 3:
		case 5:
		case 13:
		case 23:
		case 25:
			return 'produit'.$plur;
			break;
		case 4:
			return 'source'.$plur;
			break;
		case 6:
			return 'famille_mol.$plur';
			break;
		case 7:
		case 8:
		case 9:
		case 10:
		case 18:
			return 'allergene'.$plur;
			break;
	}
	return "type item $id_type_item";	
}
/**
 * Enter description here...
 *
 * @param unknown_type $type
 * @param unknown_type $tous
 * @return unknown
 */
function allerdata_id_type_item($type,$tous=false){
	switch ($type){
		case 'famille_mol':
			return array(6);
			break;
		case 'famille_taxo':
			return array(2);
			break;
		case 'source':
			return array(4);
			break;
		case 'produit':
			if ($tous)
				return array(3,5,13,23,25);
			else
				return array(3,5);
			break;
		case 'produit_en_attente':
			return array(13,23,25);
			break;
		case 'allergene':
			return array(7,8,9,10,18);
			break;
	}
	return array();	
}

function allerdata_item_racines($id_item){
	// trouver les parents
	if ($parents = sql_allfetsel('i.id_item','tbl_est_dans AS ed JOIN tbl_items AS i ON i.id_item=ed.id_est_dans','ed.id_item='.intval($id_item))){
		$parents = array_map('reset',$parents);
		$parents = array_map('allerdata_item_racines',$parents);
		return call_user_func_array('array_merge',$parents);
	}
	else
		// c'est une racine !
		return array($id_item);
}
?>