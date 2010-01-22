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
include_spip('inc/biblio');

function formulaires_editer_cohorte_charger_dist($id_groupes_patient='new', $id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('tbl_groupes_patient',$id_groupes_patient,$id_bibliographie,$lier,$retour,$config_fonc,$row,$hidden);

	$valeurs['tests_individuels'] = '';
	$valeurs['tests_quantitatifs'] = '';
	if (intval($id_groupes_patient)){
		$valeurs['tests_individuels'] = 1-intval($valeurs['pool']);
		$valeurs['tests_quantitatifs'] = 1-intval($valeurs['qualitatif']);
	}
	// determiner le nom si c'est une nouvelle cohorte !
	if (!intval($id_groupes_patient)){
		$numero = 0;
		$res = sql_select("nom","tbl_groupes_patients","id_bibliographie=".intval($id_bibliographie));
		while ($row=sql_fetch($res)){
			$nom = explode("-",$row['nom']);
			if (count($nom)==2)
				$numero = max($numero,intval(end($nom)));
		}
		$numero++;
		$valeurs['nom'] = "$id_bibliographie-$numero";
	}

	$valeurs['confirmer_pays_vide'] = '';
	$valeurs['confirmer_nb_sujets_vide'] = '';
	
	return $valeurs;
}

function formulaires_editer_cohorte_verifier_dist($id_groupes_patient='new', $id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$oblis = array();
	if (!_request('inexploitable'))
		$oblis[] = 'description';

	$erreurs = formulaires_editer_objet_verifier('tbl_groupes_patient',$id_groupes_patient,$oblis);

	// verifier qu'on a bien une biblio de referencee !
	if (!_request('id_bibliographie'))
		$erreurs['message_erreur'] = _T('editer_cohorte:aucune_biblio_definie');
		
	if (_request('inexploitable')){
		// on ne peut pas cocher cette case et en meme temps une des deux autres
		if (_request('tests_individuels') OR _request('tests_quantitatifs')){
			$erreurs['inexploitable'] = _T('editer_cohorte:incoherent');
		}
		// on ne peut pas cocher cette case si on sa des RC !
		elseif(intval($id_groupes_patient)) {
			if (sql_countsel('tbl_reactions_croisees','statut!=\'poubelle\' AND id_groupes_patient='.intval($id_groupes_patient)))
				$erreurs['inexploitable'] = _T('editer_cohorte:incoherent_RC_existent');
		}
	}

	if (!_request('pays') AND !_request('confirmer_pays_vide'))
			$erreurs['pays'] = _T('editer_cohorte:confirmer_pays_vide')."<input type='checkbox' name='confirmer_pays_vide' class='checkbox' value='1' />";
	if (!_request('nb_sujets') AND !_request('confirmer_nb_sujets_vide'))
			$erreurs['nb_sujets'] = _T('editer_cohorte:confirmer_nb_sujets_vide')."<input type='checkbox' name='confirmer_nb_sujets_vide' class='checkbox' value='1' />";


	if (count($erreurs)){
		set_request('tests_individuels',intval(_request('tests_individuels')));
		set_request('tests_quantitatifs',intval(_request('tests_quantitatifs')));
		set_request('inexploitable',intval(_request('inexploitable')));
	}

	return $erreurs;
}


function formulaires_editer_cohorte_traiter_dist($id_groupes_patient='new', $id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	
	set_request('pool',1-intval(_request('tests_individuels')));
	set_request('qualitatif',1-intval(_request('tests_quantitatifs')));
	set_request('inexploitable',intval(_request('inexploitable')));


	// vilain hack
	set_request('action','editer_tbl_groupes_patient');
	// hop traitons tout cela
	$res = formulaires_editer_objet_traiter('tbl_groupes_patient',$id_groupes_patient,0,$lier,$retour,$config_fonc,$row,$hidden);

	// si c'est une creation qui a reussi et qu'on a pas coche 'pas de RC'
	// rester sur la meme page pour permettre la saisie des RC
	if (!$res['message_erreur']
		AND !intval($id_groupes_patient)
		AND !intval(_request('inexploitable'))){
		$id = $res['id_groupes_patient'];
		$res['redirect'] = parametre_url(self(),'edit',$id);
		$res['redirect'] = ancre_url($res['redirect'],"formulaire_editer_reactions_croisees-$id");
	}
	elseif (!$res['message_erreur'] AND $retour){
		$retour = parametre_url($retour,'retour|debutc_publie_prop|debutc_poubelle', '');
		$debut = "debutc_publie_prop";
		if (sql_getfetsel("statut", "tbl_groupes_patients", "id_groupes_patient=".intval($res['id_groupes_patient']))=='poubelle')
			$debut = "debutc_poubelle";
		$res['redirect'] = ancre_url(parametre_url($retour,$debut,'@'.$res['id_groupes_patient']),'cohorte'.$res['id_groupes_patient']);
	}

	return $res;
}


?>