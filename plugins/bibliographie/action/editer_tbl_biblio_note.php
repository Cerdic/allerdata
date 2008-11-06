<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_biblio_note_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	
	if (!$id_biblio_note = _request('id_biblio_note'))
		$id_biblio_note = $arg;
	// si id_biblio_note n'est pas un nombre, c'est une creation 
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_biblio_note = intval($id_biblio_note)) {
		$id_biblio_note = insert_tbl_biblio_note();
		if (!$id_biblio_note) return array(0,_L('impossible d\'ajouter une note biblio'));
	}
	else 
		if (!autoriser('modifier','biblio_note',$id_biblio_note))
			$id_biblio_note = 0;
	
		// Enregistre l'envoi dans la BD
		if ($id_biblio_note > 0) $err = tbl_biblio_notes_set($id_biblio_note);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_biblio_note', $id_biblio_note, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_biblio_note,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_biblio_note
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_biblio_notes_set
function tbl_biblio_notes_set($id_biblio_note) {
	$err = '';

	$c = array();
	foreach (array(
		"texte","id_auteur",'id_bibliographie',
	) as $champ)
		$c[$champ] = _request($champ);

	include_spip('inc/modifier');
	revision_tbl_biblio_note($id_biblio_note, $c);

	return $err;
}

function insert_tbl_biblio_note() {

	$set = array(
		'date' => 'NOW()',
	);

	// faire une insertion a la fin, avec l'autoincrement
	$id_biblio_note = sql_insertq("tbl_biblio_notes", $set);

	return $id_biblio_note;
}

// Enregistre une revision d'item
function revision_tbl_biblio_note ($id_biblio_note, $c=false) {

	modifier_contenu('tbl_biblio_note', $id_biblio_note,
		array(
			'date_modif' => 'date' // champ a mettre a NOW() s'il y a modif
		),
		$c);

	return ''; // pas d'erreur
}

?>