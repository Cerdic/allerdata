<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_allergene_charger_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';

	include_spip('inc/allerdata_arbo');
	$valeurs['id_parent'] = allerdata_les_parents($id_item);
	if (!count($valeurs['id_parent'])) 
		$valeurs['id_parent'] =	array($id_parent);
	return $valeurs;
}

function formulaires_editer_allergene_verifier_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_item',$id_item,intval($it_item)?array('nom','commentaires'):array('nom'));
	
	return $erreurs;
}


function formulaires_editer_allergene_traiter_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	foreach(array('ccd_possible','testable','iuis','glyco') as $check)
	if (!_request($check))
		set_request($check,0);
	// vilain hack
	set_request('action','editer_tbl_item');
	// hop traitons tout cela
	return formulaires_editer_objet_traiter('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
}


?>