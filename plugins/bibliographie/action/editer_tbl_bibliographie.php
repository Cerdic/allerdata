<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_bibliographie_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// si id_bibliographie n'est pas un nombre, c'est une creation 
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_bibliographie = intval($arg)) {
		$id_bibliographie = insert_tbl_bibliographie();
		if (!$id_bibliographie) return array(0,_L('impossible d\'ajouter une bibliographie'));
	}

	// Enregistre l'envoi dans la BD
	if ($id_bibliographie > 0) $err = tbl_bibliographies_set($id_bibliographie);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_bibliographie', $id_bibliographie, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_bibliographie,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_bibliographie
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_bibliographies_set
function tbl_bibliographies_set($id_bibliographie) {
	$err = '';

	$c = array();
	foreach (array(
		"auteurs","titre",'id_journal','annee','volume','premiere_page','derniere_page','numero','supplement',
		"url","autre_media","abstract","url_full_text",'full_text_disponible','sans_interet',
		'citation',	"doublons_refs",
	) as $champ)
		$c[$champ] = _request($champ);

	include_spip('inc/modifier');
	revision_tbl_bibliographie($id_bibliographie, $c);

	// Modification du statut ?
	$c = array();
	foreach (array(
		'date', 'statut'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_bibliographie($id_bibliographie, $c);

	return $err;
}

function insert_tbl_bibliographie() {

	$set = array(
		'id_version' => -2, // indiquer une creation
		'date' => 'NOW()',
		//'statut'=>'publie', // pour le moment
	);

	// faire une insertion a la fin, avec l'autoincrement
	$id_bibliographie = sql_insertq("tbl_bibliographies", $set);

	return $id_bibliographie;
}

// Enregistre une revision d'item
function revision_tbl_bibliographie ($id_bibliographie, $c=false) {

	modifier_contenu('tbl_bibliographie', $id_bibliographie,
		array(
			'nonvide' => array('nom' => _T('info_sans_titre')),
			'date_modif' => 'date' // champ a mettre a NOW() s'il y a modif
		),
		$c);

	return ''; // pas d'erreur
}

/**
 * changer le(s) parent(s) d'un item
 *
 * @param int $id_bibliographie
 * @param array $c
 * @return unknown
 */
function instituer_tbl_bibliographie($id_bibliographie, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');
	include_spip('inc/allerdata_arbo');

	$champs = array();

	$statut_ancien = sql_getfetsel('statut','tbl_bibliographies','id_bibliographie='.intval($id_bibliographie));
	if ($statut = $c['statut']
	 AND $statut!=$statut_ancien)
		$champs['statut'] = $statut;

	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_bibliographies',
				'table_objet' => 'tbl_bibliographies',
				'spip_table_objet' => 'tbl_bibliographies',
				'id_objet' => $id_bibliographie,
				'type' => 'tbl_bibliographie',
				'action'=>'instituer'
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	if (count($champs))
		sql_updateq('tbl_bibliographies',$champs,'id_bibliographie='.intval($id_bibliographie));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_bibliographie/$id_bibliographie'");

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'tbl_bibliographies',
				'id_objet' => $id_bibliographie
			),
			'data' => $champs
		)
	);

	// Notifications
	return ''; // pas d'erreur
}

?>