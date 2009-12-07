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
	
	
	// verifier qu'un allergene n'existe pas deja avec ce nom, dans le meme produit ou la meme source
	include_spip('allerdata_fonctions');
	include_spip('penta_fonctions');
	// trouver le produit de l'allergene
	$id_produit = sql_getfetsel('id_item','tbl_items',array(sql_in('id_type_item',allerdata_id_type_item('produit',true)),sql_in('id_item',_request('id_parent'))));
	if (!$id_produit){
		$erreurs['id_parent'] = _T('editer_allergene:produit_obligatoire');
	}
	// trouver la source de l'allergene
	$id_source = penta_ascendant_le_plus_proche($id_produit,'source');
	// chercher le meme nom dans le meme produit ou la meme source
	if (!_request('confirmer_allergene_1') AND !_request('confirmer_allergene_4')){
		if ($rows = sql_allfetsel("id_item,nom",'tbl_items',array(
			sql_in('id_type_item',allerdata_id_type_item('allergene',true)),
		  "nom=".sql_quote(_request('nom'))." AND NOT(id_item=".intval($id_item).")"))
		  ){

		  // meme produit
			$liens = array();
			foreach($rows as $row){
				if ($id_produit == penta_ascendant_le_plus_proche($row['id_item'],'produit'))
					$liens[] = "<a href='".generer_url_ecrire('allerdata','page=allergenes&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
			}
			if (count($liens)){
				$liens = implode(", ",$liens);
				$erreurs['nom'] = _T("editer_allergene:item_deja_existant_avec_meme_nom").$liens
				  ."<br />"._T('editer_allergene:confirmer_ajout_allergne')."<input type='checkbox' name='confirmer_allergene_1' class='checkbox' value='1' />";
			}
			
			if (!$erreurs['nom']){
			  // meme source
			  $liens = array();
				foreach($rows as $row){
					if ($id_source == penta_ascendant_le_plus_proche($row['id_item'],'source'))
						$liens[] = "<a href='".generer_url_ecrire('allerdata','page=allergenes&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
				}
				if (count($liens)){
					$liens = implode(", ",$liens);
					$erreurs['nom'] = _T("editer_allergene:item_deja_existant_avec_meme_nom_meme_source").$liens
					  ."<br />"._T('editer_allergene:confirmer_ajout_allergne')."<input type='checkbox' name='confirmer_allergene_4' class='checkbox' value='1' />";
				}
			}
		}
	}
	// chercher la meme fonction dans le meme produit
	if (_request('fonction_classification') AND !_request('confirmer_allergene_2')){
		if ($rows = sql_allfetsel("id_item,fonction_classification,nom",'tbl_items',array(
			sql_in('id_type_item',allerdata_id_type_item('allergene',true)),
		  "fonction_classification=".sql_quote(_request('fonction_classification'))." AND NOT(id_item=".intval($id_item).")"))
		  ){
			$liens = array();
			foreach($rows as $row){
				if ($id_produit == penta_ascendant_le_plus_proche($row['id_item'],'produit'))
					$liens[] = "<a href='".generer_url_ecrire('allerdata','page=allergenes&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
			}
			if (count($liens)){
				$liens = implode(", ",$liens);
				$erreurs['fonction_classification'] = _T("editer_allergene:item_deja_existant_avec_meme_fonction_classification").$liens
					  ."<br />"._T('editer_allergene:confirmer_ajout_allergne')."<input type='checkbox' name='confirmer_allergene_2' class='checkbox' value='1' />";
			}
		}
	}
	// chercher la meme famille mol dans le meme produit
	$id_famille_mol = sql_getfetsel('id_item','tbl_items',array(sql_in('id_type_item',allerdata_id_type_item('famille_mol',true)),sql_in('id_item',_request('id_parent'))));
	include_spip('inc/allerdata_arbo');
	if (!_request('confirmer_allergene_3')){
		$meme_famille = allerdata_les_enfants($id_famille_mol,'allergene');
		$meme_produit = allerdata_les_enfants($id_produit,'allergene');
		if ($meme = array_intersect($meme_famille,$meme_produit)
		AND $meme = array_diff($meme,array(intval($id_item)))
		AND $rows = sql_allfetsel("id_item,nom",'tbl_items',sql_in('id_item',$meme))){
			$liens = array();
			foreach($rows as $row){
				$liens[] = "<a href='".generer_url_ecrire('allerdata','page=allergenes&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
			}
			$liens = implode(", ",$liens);
			$erreurs['id_parent_famille_mol'] = _T("editer_allergene:item_deja_existant_avec_meme_famille").$liens
			  ."<br />"._T('editer_allergene:confirmer_ajout_allergne')."<input type='checkbox' name='confirmer_allergene_3' class='checkbox' value='1' />";
		}
	}
	
	
	
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