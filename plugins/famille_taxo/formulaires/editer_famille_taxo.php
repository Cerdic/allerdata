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

function formulaires_editer_famille_taxo_charger_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$hidden .= "<input type='hidden' name='id_type_item' value='2' />";
	$valeurs = formulaires_editer_objet_charger('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	$valeurs['commentaires'] = '';
	return $valeurs;
}

function formulaires_editer_famille_taxo_verifier_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_item',$id_item,intval($id_item)?array('nom_fr','commentaires'):array('nom_fr'));
	
	// verifier qu'une famille taxo n'existe pas deja avec ce nom
	if ($rows = sql_allfetsel("id_item,nom",'tbl_items',
	  "id_type_item=2 AND nom_fr=".sql_quote(_request('nom_fr'))." AND NOT(id_item=".intval($id_item).")")
	  ){
		$liens = array();
		foreach($rows as $row){
			$liens[] = "<a href='".generer_url_ecrire('allerdata','page=famille_taxos&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom_fr']."</a>";
		}
		$liens = implode(", ",$liens);
		$erreurs['nom_fr'] = _T("editer_famille_taxo:item_deja_existant_avec_meme_nom").$liens;
	}
	
	include_spip('allerdata_fonctions');
	allerdata_multiplexe_erreurs_trad('nom',$erreurs);
	allerdata_multiplexe_erreurs_trad('autre_nom',$erreurs);
	return $erreurs;
}


function formulaires_editer_famille_taxo_traiter_dist($id_item='new', $id_parent=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	// vilain hack
	set_request('action','editer_tbl_item');
	// hop traitons tout cela
	$res = formulaires_editer_objet_traiter('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	if (!$res['message_erreur'] AND $retour){
		$retour = parametre_url($retour,'retour|debut_items|debut_items_poubelle|debut_items_attente', '');
		$debut = "debut_items";
		$row = sql_fetsel("statut, id_type_item",'tbl_items','id_item='.intval($res['id_item']));
		if ($row['statut']=='poubelle')
			$debut = "debut_items_poubelle";
		//elseif(in_array($row['id_type_item'],allerdata_id_type_item('famille_taxo_en_attente')))
		//	$debut = "debut_items_attente";
		$res['redirect'] = ancre_url(parametre_url($retour,$debut,'@'.$res['id_item']),'item'.$res['id_item']);
	}
	return $res;
}


?>