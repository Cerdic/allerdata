<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Autorisation de supprimer un item :
 * etre admin non restreint et l'item n'a pas d'enfant
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_item_supprimer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// verifier que le statut n'est pas deja poubelle
	if ($statut=$options['statut']
	 OR $statut = sql_getfetsel('statut','tbl_items','id_item='.intval($id)))
		if ($statut=='poubelle') return false;
	include_spip('allerdata_fonctions');
	return allerdata_item_sans_enfant($id);
}

function autoriser_item_instituer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// si le statut est non publie, on autorise !
	$statut = sql_getfetsel('statut','tbl_items','id_item='.intval($id));
	if ($statut!=='publie') return true;
	return autoriser('supprimer','item',$id,$qui,array('statut'=>$statut));
}


/**
 * Autorisation de modifier un item :
 * etre admin non restreint
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_item_modifier($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	return true;
}

/**
 * Autorisation de modifier un item :
 * etre admin non restreint
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_item_creer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	return true;
}

?>