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

function formulaires_editer_reactions_croisees_charger_dist($id_groupes_patient){
	$valeurs = array();

	if (intval($id_groupes_patient)){
		$res = sql_select('*','tbl_reactions_croisees','id_groupes_patient='.intval($id_groupes_patient),'','id_reactions_croisee');
		$valeurs["id_produit1-new"] = '';
		$valeurs["produit1-new"] = '';
		$valeurs["id_produit2-new"] = '';
		$valeurs["produit2-new"] = '';
		$valeurs["niveau_RC_sens1-new"] = '';
		$valeurs["niveau_RC_sens2-new"] = '';
		$valeurs["remarques-new"] = '';
		while ($row = sql_fetch($res)){
			$id = $row['id_reactions_croisee'];
			foreach(array('id_produit1','id_produit2','niveau_RC_sens1','niveau_RC_sens2','remarques') as $c){
				$valeurs["$c-$id"] = $row[$c];
				$valeurs['_hidden'] .= "<input type='hidden' name='_ctrl_$c-$id' value='".md5($row[$c])."' />";
			}
			// recuperer les nom des produits
			// purement indicatif -> sans valeur de controle
			$n = sql_fetsel('nom_court,nom','tbl_items','id_item='.intval($row['id_produit1']));
			$valeurs["produit1-$id"] = $n['nom_court']?$n['nom_court']:$n['nom'];
			$n = sql_fetsel('nom_court,nom','tbl_items','id_item='.intval($row['id_produit2']));
			$valeurs["produit2-$id"] = $n['nom_court']?$n['nom_court']:$n['nom'];
			
			// proposer le dernier produit1 pour la prochaine saisie
			$valeurs["id_produit1-new"] = $valeurs["id_produit1-$id"];
			$valeurs["produit1-new"] = $valeurs["produit1-$id"];

			// ajouter cet id aux rc
			$valeurs['_liste_rc'][] = $id;
		}
		$valeurs['_liste_rc'][] = 'new';
		
	}
	return $valeurs;
}


function formulaires_editer_reactions_croisees_verifier_dist($id_groupes_patient){
	$erreurs = array();
	$liste_des_rc = explode(',',_request('_liste_rc'));
	
	// regarder si on doit ajouter une nouvelle ligne !
	$ajoute_new = (_request('niveau_RC_sens1-new') OR _request('niveau_RC_sens2-new') OR _request('produit2-new') OR _request('remarques-new') OR _request('risque_CCD-new'));

	foreach($liste_des_rc as $id_rc){
		if (intval($id_rc) OR $ajoute_new){
			foreach (array('id_produit1','id_produit2','niveau_RC_sens1') as $obli)
				if (!_request("$obli-$id_rc"))
					$erreurs[preg_replace(',^id_,','',"$obli-$id_rc")] = _T('info_obligatoire');
			// verifier le format des niveau_RC_sensx
			foreach (array('niveau_RC_sens1','niveau_RC_sens2') as $check){
				if (strlen($rc = trim(_request("$check-$id_rc")))
				AND !preg_match(",^([+0]|[0-9]+/[0-9]+)$,",$rc))
					$erreurs["$check-$id_rc"] = _T('editer_cohorte:format_rc_invalide');
			}
			if (intval($id_rc)){
				// verifier les saisies concourantes eventuelles :
				$row = sql_select('*','tbl_reactions_croisees','id_groupes_patient='.intval($id_groupes_patient)." AND id_reactions_croisee=".intval($id_rc),'','id_reactions_croisee');
				foreach(array('id_produit1','id_produit2','niveau_RC_sens1','niveau_RC_sens2','remarques') as $c){
					if (md5(_request("$c-$id_rc"))!=_request("_ctrl_$c-$id_rc")
					AND _request("_ctrl_$c-$id_rc")!=md5($row[$c]))
						$erreurs["$c-$id_rc"] = _T('editer_cohorte:saisie_concourante')." ".$row[$c];
				}
			}
		}
	}

	return $erreurs;
}
/*

function formulaires_editer_cohorte_traiter_dist($id_groupes_patient='new', $id_bibliographie=0, $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	
	set_request('pool',1-intval(_request('tests_individuels')));
	set_request('qualitatif',1-intval(_request('tests_quantitatifs')));
	set_request('inexploitable',intval(_request('inexploitable')));

	// vilain hack
	set_request('action','editer_tbl_groupes_patient');
	// hop traitons tout cela
	return formulaires_editer_objet_traiter('tbl_groupes_patient',$id_groupes_patient,0,$lier,$retour,$config_fonc,$row,$hidden);
}
*/

?>