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

	$oblis = array();
	if (!in_array(_request('id_type_item'),array(23,25)))
		$oblis[] = 'nom_fr';

	if (intval($id_item))
		$oblis[] = 'commentaires';

	$erreurs = formulaires_editer_objet_verifier('tbl_item',$id_item,$oblis);

	include_spip('allerdata_fonctions');
	foreach(allerdata_langues() as $l){
		if ((strlen(_request('nom_court_'.$l))>25
		 OR (!_request('nom_court_'.$l) AND strlen(_request('nom_'.$l))>25))
		 // le nom court n'est pas necessaire pour les produits en attente
		 AND (!in_array(_request('id_type_item'),array(23,25))))
			$erreurs['nom_court_'.$l] = _T('editer_produit:erreur_nom_court_trop_long');
	}

	verifier_produit_coherent($id_item,_request('id_type_item'),_request('id_parent'),$erreurs);

	include_spip('allerdata_fonctions');
	allerdata_multiplexe_erreurs_trad('nom',$erreurs);
	allerdata_multiplexe_erreurs_trad('nom_court',$erreurs);
	allerdata_multiplexe_erreurs_trad('chaine_alpha',$erreurs);
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
	$res = formulaires_editer_objet_traiter('tbl_item',$id_item,$id_parent,$lier,$retour,$config_fonc,$row,$hidden);
	if (!$res['message_erreur'] AND $retour){
		$retour = parametre_url($retour,'retour|debut_items|debut_items_poubelle|debut_items_attente', '');
		$debut = "debut_items";
		$row = sql_fetsel("statut, id_type_item",'tbl_items','id_item='.intval($res['id_item']));
		if ($row['statut']=='poubelle')
			$debut = "debut_items_poubelle";
		//elseif(in_array($row['id_type_item'],allerdata_id_type_item('produit_en_attente')))
		//	$debut = "debut_items_attente";
		$res['redirect'] = ancre_url(parametre_url($retour,$debut,'@'.$res['id_item']),'item'.$res['id_item']);
	}
	return $res;
}


function verifier_produit_coherent($id_item,$id_type_item,$id_parent,&$erreurs){
		// verifier qu'un produit n'existe pas deja avec ce nom
	include_spip('allerdata_fonctions');
	if ($rows = sql_allfetsel("id_item,nom_fr AS nom",'tbl_items',array(
		sql_in('id_type_item',allerdata_id_type_item('produit',true)),
	  "nom_fr=".sql_quote(_request('nom_fr'))." AND NOT(id_item=".intval($id_item).")"))
	  ){
		$liens = array();
		foreach($rows as $row){
			$liens[] = "<a href='".generer_url_ecrire('allerdata','page=produits&id_item='.$row['id_item'])."' title='#".$row['id_item']."'>".$row['nom']."</a>";
		}
		$liens = implode(", ",$liens);
		$erreurs['nom_fr'] = _T("editer_produit:item_deja_existant_avec_meme_nom").$liens;
	}
	elseif (in_array($id_type_item,array(5,13))){
		$trace = "";
		// trouver la categorie du produit
		$id_type_item = allerdata_id_type_item('categorie_produit');
		$categorie = sql_fetsel("id_item,nom",allerdata_vue('tbl_items',$GLOBALS['spip_lang']),sql_in('id_type_item',$id_type_item)." AND ".sql_in('id_item',$id_parent));
		$id_cat = $categorie['id_item'];
		$trace .= "categorie : $id_cat<br />";

		// trouver la source du produit
		$id_type_item = allerdata_id_type_item('source');
		$source = sql_fetsel("id_item,nom",allerdata_vue('tbl_items',$GLOBALS['spip_lang']),sql_in('id_type_item',$id_type_item)." AND ".sql_in('id_item',$id_parent));
		$id_source = $source['id_item'];
		$nom_source = $source['nom'];
		$trace .= "source :$id_source $nom_source<br />";

		// trouver les autres sources ressemblantes
		$nom_source = explode(' ',$nom_source);
		$nom_source = reset($nom_source);
		$id_type_item = allerdata_id_type_item('source');
		$sources = sql_allfetsel("id_item,nom",allerdata_vue('tbl_items',$GLOBALS['spip_lang']),sql_in('id_type_item',$id_type_item)." AND nom LIKE ".sql_quote("$nom_source %"));
		$trace .= "sources :".implode(',',array_map('end',$sources))."<br />";
		$sources = array_map('reset',$sources);

		// trouver les autres produits dans la meme categorie
		include_spip('inc/allerdata_arbo');
		$frerescat = allerdata_les_enfants($id_cat,'produit');
		$trace .= "meme cat :".implode(',',$frerescat)."<br />";
		// trouver les autres produits dans une source semblable (meme debut de nom)
		$sourcessemblables = allerdata_les_enfants($sources,'produit',false);
		$trace .= "source semb :".implode(',',$sourcessemblables)."<br />";

		#$erreurs['nom'] .= $trace;
		// intersection, et soustraction de l'item present
		$semblables = array_intersect($frerescat, $sourcessemblables);
		$semblables = array_diff($semblables,array($id_item));
		sort($semblables);
		if (count($semblables) AND implode(',',$semblables)!==_request('check_generique')){
			$erreurs['nom'] .= recuperer_fond('modeles/produits_semblables',array('items'=>$semblables));
		}
	}
}

?>