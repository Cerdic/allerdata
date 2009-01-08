<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


/**
 * Autorisation de supprimer une cohorte
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_cohorte_supprimer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// verifier que le statut n'est pas deja poubelle
	if ($statut=$options['statut']
	 OR $statut = sql_getfetsel('statut','tbl_groupes_patients','id_groupes_patient='.intval($id)))
		if ($statut=='poubelle') return false;
	// interdit si la cohorte a des RC
	if (sql_countsel('tbl_reactions_croisees','id_groupes_patient='.intval($id)))
		return false;
	return true;
}


/**
 * Autorisation de changer de statut une cohorte
 * necessite d'etre admin, et qu'elle soit inutilisee
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_cohorte_instituer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// si le statut est deja poubelle, on autorise !
	$statut = sql_getfetsel('statut','tbl_groupes_patients','id_groupes_patient='.intval($id));
	if ($statut=='poubelle') return true;
	return autoriser('supprimer','cohorte',$id,$qui,array('statut'=>$statut));
}


/**
 * Declaration des champs pour la recherche sur la tbl_items
 * utilisee dans le back office
 *
 * @param array $liste
 * @return array
 */
function cohorte_rechercher_liste_des_champs($liste){
	$liste['tbl_groupes_patient'] = array('nom'=>4,'description'=>1);
	return $liste;
}


?>