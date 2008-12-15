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

function formulaires_editer_famille_mol_charger_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$hidden .= "<input type='hidden' name='id_type_item' value='6' />";
	$valeurs = formulaires_editer_objet_charger('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';
	return $valeurs;
}

function formulaires_editer_famille_mol_verifier_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_item',$id_item,intval($it_item)?array('nom','commentaires'):array('nom'));
	if ($url = _request('url')){
		include_spip('inc/distant');
		if (!recuperer_page($url)){
			$erreurs['url'] = "Url invalide : <a href='$url'>$url</a>";
		}
	}
	
	// verifier qu'une famille moleculaire n'existe pas deja avec ce nom
	if ($rows = sql_allfetsel("id_item,nom",'tbl_items',
	  "id_type_item=6 AND nom=".sql_quote(_request('nom'))." AND NOT(id_item=".intval($id_item).")")
	  ){
		$liens = array();
		foreach($rows as $row){
			$liens[] = "<a href='".generer_url_ecrire('allerdata','page=famille_mols&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
		}
		$liens = implode(", ",$liens);
		$erreurs['nom'] = _T("editer_famille_mol:item_deja_existant_avec_meme_nom").$liens;
	}

	return $erreurs;
}


function formulaires_editer_famille_mol_traiter_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	// vilain hack
	set_request('action','editer_tbl_item');
	// hop traitons tout cela
	return formulaires_editer_objet_traiter('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
}


?>