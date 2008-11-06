<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_biblio_note_charger_dist($id_note='new',$id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){

	$valeurs = formulaires_editer_objet_charger('tbl_biblio_note',$id_note,0,$lier,$retour,$config_fonc,$row,$hidden);
	if (!$valeurs['id_bibliographie'])
		$valeurs['id_bibliographie'] = $id_bibliographie;
	return $valeurs;
}

function formulaires_editer_biblio_note_verifier_dist($id_note='new',$id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_biblio_note',$id_note,array('texte'));
	return $erreurs;
}

function formulaires_editer_biblio_note_traiter_dist($id_note='new',$id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$id_biblio_note = $id_note;
	if (_request('id_biblio_note')!=$id_biblio_note
	  AND autoriser('modifier','biblio_note',_request('id_biblio_note')))
	  $id_biblio_note = _request('id_biblio_note');
	// vilain hack
	set_request('action','editer_tbl_biblio_note');
	$res = formulaires_editer_objet_traiter('tbl_biblio_note',$id_biblio_note,0,$lier,$retour,$config_fonc,$row,$hidden);
	// preparez la saisie de la note suivante !
	$res['editable'] = true;
	set_request('texte','');
	return $res;
}


?>