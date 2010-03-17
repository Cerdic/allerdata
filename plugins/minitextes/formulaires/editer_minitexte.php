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
	$valeurs = formulaires_editer_objet_charger('tbl_minitextes',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';

	$valeurs['id_items'] = array();
	$valeurs['id_item'] = '';
	$valeurs['id_item_1'] = '';
	$valeurs['id_item_2'] = '';

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


?>