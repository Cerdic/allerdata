<?php
/*
 * Plugin minitexte / Admin du site
 * Licence GPL
 * (c) 2010 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_minitexte_charger_dist($id_minitexte='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('tbl_minitextes',$id_minitexte,0,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';

	$valeurs['id_items'] = array();
	$valeurs['id_item'] = '';
	$valeurs['id_item_1'] = '';
	$valeurs['id_item_2'] = '';

	$valeurs['type'] = _request($type)?_request($type):$valeurs['type'];
	if ($valeurs['type']==1){
		if ($id_minitexte = intval($id_minitexte)){
				$valeurs['id_items'] = array_map('reset',sql_allfetsel("id_item", "tbl_items", "id_minitexte=".intval($id_minitexte)." AND ".sql_in('id_type_item',allerdata_id_type_item('produit_et_categorie'))));
		}
	}
	if ($valeurs['type']==2){
		$item = sql_fetsel("*", "tbl_minitextes_items", "id_minitexte=".intval($id_minitexte));
		$valeurs['id_item_1'] = $item['id_item_1'];
		$valeurs['id_item_2'] = $item['id_item_2'];
	}
	if ($valeurs['type']==3){
		if ($id_minitexte = intval($id_minitexte))
			$valeurs['id_item'] = sql_getfetsel("id_item", "tbl_items", "id_minitexte=".intval($id_minitexte)." AND ".sql_in('id_type_item',allerdata_id_type_item('famille_mol')));
	}
	return $valeurs;
}

function formulaires_editer_minitexte_verifier_dist($id_minitexte='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$oblis = array('texte','type');
	$erreurs = formulaires_editer_objet_verifier('tbl_minitextes',$id_minitexte,$oblis);

	switch ($type=_request('type')){
		case 1:
			if (!_request('id_items'))
				$erreurs['id_items'] = _T('info_obligatoire');
			break;
		case 2:
			if (!_request('id_item_1'))
				$erreurs['id_item_1'] = _T('info_obligatoire');
			if (!_request('id_item_2'))
				$erreurs['id_item_2'] = _T('info_obligatoire');
			break;
		case 3:
			if (!_request('id_item'))
				$erreurs['id_item'] = _T('info_obligatoire');
			break;
		default:
			$erreurs['type'] = _T('info_obligatoire');
			break;
	}

	return $erreurs;
}




function formulaires_editer_minitexte_traiter_dist($id_minitexte='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	// vilain hack
	set_request('action','editer_tbl_minitexte');
	// hop traitons tout cela
	$res = formulaires_editer_objet_traiter('tbl_minitexte',$id_minitexte,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	if (!$res['message_erreur'] AND $retour){
		$retour = parametre_url($retour,'retour|debut_minitextes', '');
		$debut = "debut_minitextes";
		$row = sql_fetsel("statut, id_minitexte",'tbl_minitextes','id_minitexte='.intval($res['id_minitexte']));
		if ($row['statut']=='poubelle')
			$debut = "debut_id_minitextes_poubelle";
		//elseif(in_array($row['id_type_item'],allerdata_id_type_item('allergene_en_attente')))
		//	$debut = "debut_items_attente";
		$res['redirect'] = ancre_url(parametre_url($retour,$debut,'@'.$res['id_minitexte']),'iminitexte'.$res['id_minitexte']);
	}
	return $res;
}
?>