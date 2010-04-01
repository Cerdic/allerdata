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
include_spip('allerdata_fonctions');

function formulaires_editer_minitexte_charger_dist($id_minitexte='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('tbl_minitexte',$id_minitexte,0,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';
	$valeurs['id'] = $id_minitexte;

	$valeurs['id_items'] = array();
	$valeurs['id_item'] = '';
	$valeurs['id_item_1'] = '';
	$valeurs['id_item_2'] = '';

	// regarder si on arrive avec une pre-selection
	if ($item = _request('item')){
		$id_type_item = sql_getfetsel("id_type_item", "tbl_items", "id_item=".intval($item));
		if (($t = allerdata_type_item($id_type_item))=='produit'){
			$valeurs['id_items'][] = $item;
			$valeurs['type'] = 1;
		}
		elseif ($t == 'famille_mol'){
			$valeurs['id_item'] = $item;
			$valeurs['type'] = 3;
		}
	}
	else if ($item_1 = _request('item_1') AND $item_2 = _request('item_2')) {
		$valeurs['id_item_1'] = $item_1;
		$valeurs['id_item_2'] = $item_2;
		$valeurs['type'] = 2;
	}

	$valeurs['type'] = _request('type')?_request('type'):$valeurs['type'];
	if ($valeurs['type']==1){
		if ($r=_request('id_items'))
			$valeurs['id_items'] = $r;
		elseif ($id_minitexte = intval($id_minitexte)){
				$valeurs['id_items'] = array_map('reset',sql_allfetsel("id_item", "tbl_items", "id_minitexte=".intval($id_minitexte)." AND ".sql_in('id_type_item',allerdata_id_type_item('produit_et_categorie'))));
		}
	}
	if ($valeurs['type']==2 AND intval($id_minitexte)){
		if ($item = sql_fetsel("*", "tbl_minitextes_items", "id_minitexte=".intval($id_minitexte))){
			$valeurs['id_item_1'] = $item['id_item_1'];
			$valeurs['id_item_2'] = $item['id_item_2'];
		}
	}
	if ($valeurs['type']==3){
		if ($id_minitexte = intval($id_minitexte))
			$valeurs['id_item'] = sql_getfetsel("id_item", "tbl_items", "id_minitexte=".intval($id_minitexte)." AND ".sql_in('id_type_item',allerdata_id_type_item('famille_mol')));
	}

	$valeurs['_hidden'] .= "<input type='hidden' name='ctrl_items' value='".ctrl_md5_items($valeurs['id_items'])."' />";
	return $valeurs;
}

function formulaires_editer_minitexte_verifier_dist($id_minitexte='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$oblis = array('texte','type');
	$erreurs = formulaires_editer_objet_verifier('tbl_minitextes',$id_minitexte,$oblis);

	switch ($type=_request('type')){
		case 1:
			if (!($items = _request('id_items')))
				$erreurs['id_items'] = _T('info_obligatoire');
			else {
				$ctrl = ctrl_md5_items($items);
				if ($ctrl!=_request('ctrl_items')
					OR sql_countsel('tbl_items', 'id_minitexte>0 AND id_minitexte<>'.intval($id_minitexte)." AND ".sql_in('id_item',$items))
					)
					$erreurs['id_items'] = _T('minitext:erreur_verifier_items');
			}
			break;
		case 2:
			if (!_request('id_item_1'))
				$erreurs['id_item_1'] = _T('info_obligatoire');
			if (!_request('id_item_2'))
				$erreurs['id_item_2'] = _T('info_obligatoire');
			break;
		case 3:
			if (!($item=_request('id_item')))
				$erreurs['id_item'] = _T('info_obligatoire');
			elseif (sql_getfetsel('id_item','tbl_items', 'id_minitexte>0 AND id_minitexte<>'.intval($id_minitexte)." AND id_item=".intval($item)))
				$erreurs['id_item'] = _T('minitext:erreur_famille_mol_deja_minitexte');
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
		$debut = "debutm_publie_prop";
		$row = sql_fetsel("statut, id_minitexte",'tbl_minitextes','id_minitexte='.intval($res['id_minitexte']));
		if ($row['statut']=='poubelle')
			$debut = "debutm_poubelle";
		//elseif(in_array($row['id_type_item'],allerdata_id_type_item('allergene_en_attente')))
		//	$debut = "debut_items_attente";
		$res['redirect'] = ancre_url(parametre_url($retour,$debut,'@'.$res['id_minitexte']),'iminitexte'.$res['id_minitexte']);
	}
	return $res;
}


function ctrl_md5_items($items){
	if (!is_array($items) OR !count($items))
		return '';
	sort($items);
	return md5(implode(',',$items));
}
?>