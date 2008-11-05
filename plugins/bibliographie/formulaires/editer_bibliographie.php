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
include_spip('inc/biblio');

function formulaires_editer_bibliographie_charger_dist($id_bibliographie='new', $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){

	$valeurs = formulaires_editer_objet_charger('tbl_bibliographie',$id_bibliographie,0,$lier,$retour,$config_fonc,$row,$hidden);
/*	var_dump(biblio_extrait_auteurs($valeurs['auteurs']));
	if (biblio_extrait_auteurs($valeurs['auteurs'])){
	$res = sql_select('id_bibliographie,auteurs','tbl_bibliographies');
	while ($row = sql_fetch($res)){
		if ($row['auteurs'] AND !biblio_extrait_auteurs($row['auteurs']))
			echo (":".$row['id_bibliographie'].':'.$row['auteurs'].':<br />');
	}
	}
	else echo (":".$valeurs['id_bibliographie'].':'.$valeurs['auteurs'].':<br />');*/
	$liste = biblio_trouver_sembables($valeurs['auteurs'],$valeurs['titre'],$valeurs['autre_media'],$valeurs['id_journal'],$valeurs['annee'],$valeurs['volume'],$valeurs['numero'],$valeurs['supplement'],$valeurs['premiere_page']);
	if (intval($id_bibliographie))
		$liste = array_diff($liste,array($id_bibliographie));
	if ($valeurs['doublons_ref']!=implode(',',$liste))
		$valeurs['_semblables'] = $liste;

	$valeurs['journal'] = sql_getfetsel('nom','tbl_journals','id_journal='.intval($valeurs['id_journal']));
	return $valeurs;
}

function formulaires_editer_bibliographie_verifier_dist($id_bibliographie='new', $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('tbl_bibliographie',$id_bibliographie,array('titre','auteurs','premiere_page'));
	
	// Verifier la syntaxe des auteurs
	if (!_request('forcer_auteurs') AND !biblio_extrait_auteurs(_request('auteurs'))){
		$erreurs['auteurs'] = _T('editer_bibliographie:confirmer_auteurs')."<input type='checkbox' name='confirmer_auteurs' class='checkbox' value='1' />";
	}
	// verifier que le journal existe et est non ambigu
	if (!strlen($j = _request('journal'))){
		set_request('id_journal',0);
	}
	else if($liste = biblio_rechercher_journal($j)){
		if (count($liste)>1)
			$erreurs['journal'] = _T('editer_bibliographie:plusieurs_journaux_correspondent');
		else
			set_request('id_journal',reset($liste));
	}
	else {
		if (!_request('ajout_journal'))
			// un journal est indique mais introuvable
			$erreurs['journal'] = _T('editer_bibliographie:confirmer_ajout_journal')."<input type='checkbox' name='confirmer_journal' class='checkbox' value='1' />";
	}

	// verifier l'annee qui est soit en chiffre superieur a 1900, soit Epub soit vide
	if (strlen($a=_request('annee')) AND !(intval($a)>1900 AND intval($a)<=date('Y')+1) AND $a!=='Epub')
			$erreurs['annee'] = _T('editer_bibliographie:incorrecte');
	
	if ($p = _request('premiere_page')
	AND $d=_request('derniere_page')
	AND is_numeric($p)
	AND is_numeric($d)
	AND $d<$p)
			$erreurs['derniere_page'] = _T('editer_bibliographie:incorrecte');
	
	foreach(array('url','url_full_text') as $c)
		if ($u=trim(_request($c))){
			if (is_numeric($u)){
				$u = _URL_PUBMED . $u;
				set_request($c,$u);
			}
			include_spip('inc/distant');
			if (!recuperer_page($u))
				$erreur[$c] = _T('editer_bibliographie:url_invalide');
		}
	
		
	// verifier si une reference ressemblante n'existe pas deja
	if (!_request('confirmer_ajout_reference')){
		$liste = biblio_trouver_sembables(_request('auteurs'),_request('titre'),_request('autre_media'),_request('id_journal'),_request('annee'),_request('volume'),_request('numero'),_request('supplement'),_request('premiere_page'));
		// enlever la reference en cours si elle est dedans
		if (intval($id_bibliographie)){
			$liste = array_diff($liste,array($id_bibliographie));
			// passer sous silence si les references ont deja ete reperees
			$doublons_connus = sql_getfetsel('doublons_refs','tbl_bibliogaphies','id_bibliographie='.intval($id_bibliographie));
			if ($doublons_connus==implode(',',$liste))
				$liste = array();
		}
		if (count($liste)){
			$erreurs['message_erreur'] = _T('editer_bibliographie:confirmer_ajout_reference')."<input type='checkbox' name='confirmer_ajout_reference' class='checkbox' value='1' />";
			$erreurs['semblables'] = $liste;
		}
		// ...
	}
	return $erreurs;
}


function formulaires_editer_bibliographie_traiter_dist($id_bibliographie='new', $retour='', $lier=0, $config_fonc='', $row=array(), $hidden=''){
	// ajout du journal si besoin
	if (strlen($j = _request('journal'))
	AND !count($liste = biblio_rechercher_journal($j))){
		$id_journal = sql_insertq('tbl_journals',array('nom'=>$j));
		set_request('id_journal',$id_journal);
	}
	$liste = biblio_trouver_sembables(_request('auteurs'),_request('titre'),_request('autre_media'),_request('id_journal'),_request('annee'),_request('volume'),_request('numero'),_request('supplement'),_request('premiere_page'));
	// enlever la reference en cours si elle est dedans
	if (intval($id_bibliographie)){
		$liste = array_diff($liste,array($id_bibliographie));
	set_request('doublons_ref',implode(',',$liste));
	
	// vilain hack
	set_request('action','editer_tbl_bibliographie');
	// hop traitons tout cela
	return formulaires_editer_objet_traiter('tbl_bibliographie',$id_bibliographie,0,$lier,$retour,$config_fonc,$row,$hidden);
}


?>