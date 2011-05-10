<?php
/*
 * Plugin minitexte / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_minitexte_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// si id_minitexte n'est pas un nombre, c'est une creation
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_minitexte = intval($arg)) {
		$id_minitexte = insert_tbl_minitexte(_request('id_minitexte'));
		if (!$id_minitexte) return array(0,_L('impossible d\'ajouter un minitexte'));
	}

	// Enregistre l'envoi dans la BD
	if ($id_minitexte > 0) $err = tbl_minitextes_set($id_minitexte);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_minitexte', $id_minitexte, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_minitexte,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_minitexte
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_minitextes_set
function tbl_minitextes_set($id_minitexte,$set=null) {
	$err = '';

	if (is_null($set)){
		include_spip('inc/allerdata_fonctions');
		$c = array();
		foreach (array_merge(allerdata_liste_champs_trad('texte'),array('incidence_rav'))
			as $champ)
			$c[$champ] = _request($champ);
	}
	else
		$c = $set;

	include_spip('inc/modifier');
	revision_tbl_minitexte($id_minitexte, $c);

	// Modification du statut ?
	$c = array();
	foreach (array(
		'statut',"type",'id_item','id_items','id_item_1','id_item_2',
	) as $champ)
		$c[$champ] = _request($champ,$set);
	$err .= instituer_tbl_minitexte($id_minitexte, $c);

	return $err;
}

function insert_tbl_minitexte($id = 0) {

	$set = array(
		'id_version' => -2, // indiquer une creation
		'date' => 'NOW()', // date de creation
		'statut'=>'prop',
	);

	if (intval($id))
		$set['id_minitexte'] = intval($id);

	// faire une insertion a la fin, avec l'autoincrement
	// ou a un id fourni
	// et verifier que l'insertion a fonctionne
	if ($id_minitexte = sql_insertq("tbl_minitextes", $set)
		OR (isset($set['id_minitexte']) AND $id_minitexte = $set['id_minitexte']))
		$id_minitexte = sql_getfetsel('id_minitexte','tbl_minitextes','id_minitexte='.intval($id_minitexte));

	return $id_minitexte;
}

// Enregistre une revision d'item
function revision_tbl_minitexte ($id_minitexte, $c=false) {

	modifier_contenu('tbl_minitexte', $id_minitexte,
		array(
			'date_modif' => 'date' // champ a mettre a NOW() s'il y a modif
		),
		$c);

	return ''; // pas d'erreur
}

/**
 * changer le(s) parent(s) d'un minitexte
 *
 * @param int $id_minitexte
 * @param array $c
 * @return unknown
 */
function instituer_tbl_minitexte($id_minitexte, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');
	include_spip('inc/minitext');

	$id_parent_actuel = minitext_les_parents($id_minitexte);
	$champs = array();

	
	if (isset($c['statut'])){
		$statut_ancien = sql_getfetsel('statut','tbl_minitextes','id_minitexte='.intval($id_minitexte));
		if ($statut = $c['statut']
		 AND $statut!=$statut_ancien)
			$champs['statut'] = $statut;
	}
	$ancien_type = sql_getfetsel('type','tbl_minitextes','id_minitexte='.intval($id_minitexte));
	if (isset($c['type'])){
		if ($type = $c['type']
		 AND $type!=$ancien_type)
			$champs['type'] = $type;
		$champs['type'] = $c['type'];
	}
	else
		$type = $ancien_type;

	switch ($type){
		case 1:
			if ($id_parent = $c['id_items'])
				$champs['id_parent'] = array_map('reset',sql_allfetsel('id_item', "tbl_items", sql_in('id_item',$id_parent)));
			break;
		case 2:
			if ($id_parent_1 = $c['id_item_1']
				AND $id_parent_2 = $c['id_item_2']
			  AND $id_parent_1 = sql_getfetsel('id_item', 'tbl_items', 'id_item='.intval($id_parent_1))
			  AND $id_parent_2 = sql_getfetsel('id_item', 'tbl_items', 'id_item='.intval($id_parent_2))
				){
				$champs['id_parent'] = array($id_parent_1,$id_parent_2);
				sort($champs['id_parent']);
				$champs['id_parent'] = array($champs['id_parent']);
			}
			break;
		case 3:
			if ($id_parent = $c['id_item'])
				$champs['id_parent'] = array_map('reset',sql_allfetsel('id_item', "tbl_items", sql_in('id_item',$id_parent)));
			break;
	}

	if (!count($champs)) return;
	
	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_minitextes',
				'table_objet' => 'tbl_minitextes',
				'spip_table_objet' => 'tbl_minitextes',
				'id_objet' => $id_minitexte,
				'type' => 'tbl_minitexte',
				'action'=>'instituer'
			),
			'data' => $champs
		)
	);

	// Envoyer les modifs.
	if (isset($champs['id_parent'])){
		minitext_modifier_les_parents($id_minitexte,$champs['id_parent']);
		unset($champs['id_parent']);
	}

	if (!count($champs)) return;

	if (count($champs))
		sql_updateq('tbl_minitextes',$champs,'id_minitexte='.intval($id_minitexte));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_minitexte/$id_minitexte'");

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'tbl_minitextes',
				'id_objet' => $id_minitexte
			),
			'data' => $champs
		)
	);

	// Notifications
	return ''; // pas d'erreur
}

?>