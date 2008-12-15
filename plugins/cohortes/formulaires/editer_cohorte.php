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
	

	return $valeurs;
}

function formulaires_editer_cohorte_verifier_dist($id_groupes_patient='new', $id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_groupes_patient',$id_groupes_patient,array('description'));

	// verifier qu'on a bien une biblio de referencee !
	if (!_request('id_bibliographie'))
		$erreurs['message_erreur'] = _T('editer_cohorte:aucune_biblio_definie');
		
	if (_request('inexploitable')
	AND (_request('tests_individuels') OR _request('tests_quantitatifs'))){
		$erreurs['inexploitable'] = _T('editer_cohorte:incoherent');
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
	return formulaires_editer_objet_traiter('tbl_groupes_patient',$id_groupes_patient,0,$lier,$retour,$config_fonc,$row,$hidden);
}


?>