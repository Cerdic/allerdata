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

function formulaires_editer_produit_charger_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';

	include_spip('inc/allerdata_arbo');
	$valeurs['id_parent'] = allerdata_les_parents($id_item);
	if (!count($valeurs['id_parent'])) 
		$valeurs['id_parent'] =	array($id_parent);
	return $valeurs;
}

function formulaires_editer_produit_verifier_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_item',$id_item,intval($it_item)?array('nom','commentaires'):array('nom'));
	
	if (strlen(_request('nom_court'))>25
	 OR (!_request('nom_court') AND strlen(_request('nom'))>25))
	 	$erreurs['nom_court'] = _T('editer_produit:erreur_nom_court_trop_long');

	
	// verifier qu'une source n'existe pas deja avec ce nom
	include_spip('allerdata_fonctions');
	if ($rows = sql_allfetsel("id_item,nom",'tbl_items',array(
		sql_in('id_type_item',allerdata_id_type_item('produit')),
	  "nom=".sql_quote(_request('nom'))." AND NOT(id_item=".intval($id_item).")"))
	  ){
		$liens = array();
		foreach($rows as $row){
			$liens[] = "<a href='".generer_url_ecrire('allerdata','page=produits&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
		}
		$liens = implode(", ",$liens);
		$erreurs['nom'] = _T("editer_produit:item_deja_existant_avec_meme_nom").$liens;
	}
	
	return $erreurs;
}


function formulaires_editer_produit_traiter_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	if (!_request('nom_court'))
		set_request('nom_court',_request('nom'));
	if (!_request('ccd_possible'))
		set_request('ccd_possible',0);
	
	// vilain hack
	set_request('action','editer_tbl_item');
	// hop traitons tout cela
	return formulaires_editer_objet_traiter('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
}


?>