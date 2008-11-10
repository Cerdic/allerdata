<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_reactions_croisee_dist($id_reactions_croisee=0,$post=null) {

	//$securiser_action = charger_fonction('securiser_action', 'inc');
	//$arg = $securiser_action();

	// si id_reactions_croisee n'est pas un nombre, c'est une creation 
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!intval($id_reactions_croisee)
	  AND $id_groupes_patient=_request('id_groupes_patient',$post)) {
		$id_reactions_croisee = insert_tbl_reactions_croisee($id_groupes_patient);
		if (!$id_reactions_croisee) return array(0,_L('impossible d\'ajouter une RC'));
	}

	// Enregistre l'envoi dans la BD
	if ($id_reactions_croisee > 0) $err = tbl_reactions_croisees_set($id_reactions_croisee,$post);

	return array($id_reactions_croisee,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_reactions_croisee
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_reactions_croisees_set
function tbl_reactions_croisees_set($id_reactions_croisee, $post=null) {
	$err = '';

	$c = array();
	foreach (array(
		"id_produit1","molecules1",'niveau_rc_sens1','niveau_rc_sens2','id_produit2','molecules2','fleche_sens1','fleche_sens2',
		'remarques','risque_ccd',
	) as $champ)
		$c[$champ] = _request($champ, $post);

	include_spip('inc/modifier');
	revision_tbl_reactions_croisee($id_reactions_croisee, $c);

	// Modification du statut ?
	$c = array();
	foreach (array(
		'date'//, 'statut'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_reactions_croisee($id_reactions_croisee, $c);

	return $err;
}

function insert_tbl_reactions_croisee($id_groupes_patient) {

	$set = array(
		'id_version' => -2, // indiquer une creation
		'date' => 'NOW()',
		'id_groupes_patient' => $id_groupes_patient,
		//'statut'=>'publie', // pour le moment
	);

	// faire une insertion a la fin, avec l'autoincrement
	$id_reactions_croisee = sql_insertq("tbl_reactions_croisees", $set);

	return $id_reactions_croisee;
}

// Enregistre une revision d'item
function revision_tbl_reactions_croisee ($id_reactions_croisee, $c=false) {

	modifier_contenu('tbl_reactions_croisee', $id_reactions_croisee,
		array(
			'nonvide' => array('nom' => _T('info_sans_titre')),
			'date_modif' => 'date' // champ a mettre a NOW() s'il y a modif
		),
		$c);

	return ''; // pas d'erreur
}

/**
 * changer statut et id_bibliographie
 *
 * @param int $id_reactions_croisee
 * @param array $c
 * @return unknown
 */
function instituer_tbl_reactions_croisee($id_reactions_croisee, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');

	$champs = array();
/*
	$statut_ancien = sql_getfetsel('statut','tbl_reactions_croisees','id_reactions_croisee='.intval($id_reactions_croisee));
	if ($statut = $c['statut']
	 AND $statut!=$statut_ancien)
		$champs['statut'] = $statut;*/

	if ($id_groupes_patient = $c['id_groupes_patient']
	 AND $id_groupes_patient!=sql_getfetsel('id_groupes_patient','tbl_reactions_croisees','id_reactions_croisee='.intval($id_reactions_croisee)))
		$champs['id_groupes_patient'] = $id_groupes_patient;

	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_reactions_croisees',
				'table_objet' => 'tbl_reactions_croisees',
				'spip_table_objet' => 'tbl_reactions_croisees',
				'id_objet' => $id_reactions_croisee,
				'type' => 'tbl_reactions_croisee',
				'action'=>'instituer'
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	if (count($champs))
		sql_updateq('tbl_reactions_croisees',$champs,'id_reactions_croisee='.intval($id_reactions_croisee));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_reactions_croisee/$id_reactions_croisee'");

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'tbl_reactions_croisees',
				'id_objet' => $id_reactions_croisee
			),
			'data' => $champs
		)
	);

	// Notifications
	return ''; // pas d'erreur
}

?>