<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_groupes_patient_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// si id_groupes_patient n'est pas un nombre, c'est une creation 
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_groupes_patient = intval($arg)
	  AND $id_bibliographie=_request('id_bibliographie')) {
		$id_groupes_patient = insert_tbl_groupes_patient($id_bibliographie);
		if (!$id_groupes_patient) return array(0,_L('impossible d\'ajouter un groupe de patients'));
	}

	// Enregistre l'envoi dans la BD
	if ($id_groupes_patient > 0) $err = tbl_groupes_patients_set($id_groupes_patient);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_groupes_patient', $id_groupes_patient, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_groupes_patient,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_groupes_patient
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_groupes_patients_set
function tbl_groupes_patients_set($id_groupes_patient) {
	$err = '';

	$c = array();
	foreach (array(
		"nom","description",'nb_sujets','pool','qualitatif','pays','remarques','inexploitable',
	) as $champ)
		$c[$champ] = _request($champ);

	include_spip('inc/modifier');
	revision_tbl_groupes_patient($id_groupes_patient, $c);

	// Modification du statut ?
	$c = array();
	foreach (array(
		'date'//, 'statut'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_groupes_patient($id_groupes_patient, $c);

	return $err;
}

function insert_tbl_groupes_patient($id_bibliographie) {

	$set = array(
		'id_version' => -2, // indiquer une creation
		'date' => 'NOW()',
		'id_bibliographie' => $id_bibliographie,
		//'statut'=>'publie', // pour le moment
	);

	// faire une insertion a la fin, avec l'autoincrement
	$id_groupes_patient = sql_insertq("tbl_groupes_patients", $set);

	return $id_groupes_patient;
}

// Enregistre une revision d'item
function revision_tbl_groupes_patient ($id_groupes_patient, $c=false) {

	modifier_contenu('tbl_groupes_patient', $id_groupes_patient,
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
 * @param int $id_groupes_patient
 * @param array $c
 * @return unknown
 */
function instituer_tbl_groupes_patient($id_groupes_patient, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');

	$champs = array();

	if (isset($c['statut'])){
		$statut_ancien = sql_getfetsel('statut','tbl_groupes_patients','id_groupes_patient='.intval($id_groupes_patient));
		if ($statut = $c['statut']
		 AND $statut!=$statut_ancien)
			$champs['statut'] = $statut;
	}

	if ($id_bibliographie = $c['id_bibliographie']
	 AND $id_bibliographie!=sql_getfetsel('id_bibliographie','tbl_groupes_patients','id_groupes_patient='.intval($id_groupes_patient)))
		$champs['id_bibliographie'] = $id_bibliographie;

	if (!count($champs)) return;

	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_groupes_patients',
				'table_objet' => 'tbl_groupes_patients',
				'spip_table_objet' => 'tbl_groupes_patients',
				'id_objet' => $id_groupes_patient,
				'type' => 'tbl_groupes_patient',
				'action'=>'instituer'
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	if (count($champs))
		sql_updateq('tbl_groupes_patients',$champs,'id_groupes_patient='.intval($id_groupes_patient));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_groupes_patient/$id_groupes_patient'");

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'tbl_groupes_patients',
				'id_objet' => $id_groupes_patient
			),
			'data' => $champs
		)
	);

	// Notifications
	return ''; // pas d'erreur
}

?>