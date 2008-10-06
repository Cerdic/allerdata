<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_tbl_item_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// si id_item n'est pas un nombre, c'est une creation 
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_item = intval($arg)) {
		$id_item = insert_tbl_item(_request('id_type_item'));
		if (!$id_item) return array(0,_L('impossible d\'ajouter un item'));
	}

	// Enregistre l'envoi dans la BD
	if ($id_item > 0) $err = tbl_items_set($id_item);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_item', $id_item, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_item,$err);
}

// Appelle toutes les fonctions de modification d'un tbl_item
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@tbl_items_set
function tbl_items_set($id_item) {
	$err = '';

	$c = array();
	foreach (array(
		'nom', 'source', 'famille', 'autre_nom', 'nom_complet', 'chaine_alpha', 
		'interrogeable', 'testable', 'code_test', 'iuis', 'masse', 'glyco',
		'id_niveau_allergenicite', 'affichage_suggestion', 'representatif',
		'ccd_possible', 'information', 'fonction_classification', 'nom_court',
	) as $champ)
		$c[$champ] = _request($champ);

	include_spip('inc/modifier');
	revision_tbl_item($id_item, $c);

	// Modification de statut, changement de rubrique ?
	/*$c = array();
	foreach (array(
		'date_item', 'statut'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_item($id_item, $c);*/

	// Un lien de trad a prendre en compte
	#$err .= tbl_item_referent($id_item, array('lier' => _request('lier')));

	return $err;
}

function insert_tbl_item($id_type_item) {

	$id_item = sql_insertq("tbl_items", array(
		'id_type_item' => $id_type_item,
		'id_version' => 0,
		'date_item' => 'NOW()',
	));

	return $id_item;
}

// Enregistre une revision d'item
function revision_tbl_item ($id_item, $c=false) {

	modifier_contenu('tbl_item', $id_item,
		array(
			'nonvide' => array('nom' => _T('info_sans_titre')),
			'date_modif' => 'date_item' // champ a mettre a NOW() s'il y a modif
		),
		$c);

	return ''; // pas d'erreur
}


// $c est un array ('statut', 'id_parent' = changement de rubrique)
//
// statut et rubrique sont lies, car un admin restreint peut deplacer
// un tbl_item publie vers une rubrique qu'il n'administre pas
// http://doc.spip.org/@instituer_tbl_item
/*function instituer_tbl_item($id_item, $c, $calcul_rub=true) {

	include_spip('inc/autoriser');
	include_spip('inc/rubriques');
	include_spip('inc/modifier');

	$row = sql_fetsel("statut, id_rubrique", "tbl_items", "id_item=$id_item");
	$id_rubrique = $row['id_rubrique'];
	$statut_ancien = $statut = $row['statut'];
	$champs = array();
	$date = $c['date'];

	$s = $c['statut'];

	// cf autorisations dans inc/instituer_tbl_item
	if ($s AND $s != $statut) {
		if (autoriser('publierdans', 'rubrique', $id_rubrique))
			$statut = $champs['statut'] = $s;
		else if (autoriser('modifier', 'tbl_item', $id_item) AND $s != 'publie')
			$statut = $champs['statut'] = $s;
		else
			spip_log("editer_tbl_item $id_item refus " . join(' ', $c));

		// En cas de publication, fixer la date a "maintenant"
		// sauf si $c commande autre chose
		// En cas de proposition d'un tbl_item (mais pas depublication), idem
		if ($champs['statut'] == 'publie'
		OR ($champs['statut'] == 'prop'
			AND !in_array($statut_ancien, array('publie', 'prop'))
		)) {
			if (!is_null($date))
				$champs['date'] = $date;
			else {
				# on prend la date de MySQL pour eviter un decalage cf. #975
				$d = sql_fetsel('NOW() AS d');
				$champs['date'] = $d['d'];
			}
		}
	}

	// Verifier que la rubrique demandee existe et est differente
	// de la rubrique actuelle
	if ($id_parent = $c['id_parent']
	AND $id_parent != $id_rubrique
	AND (sql_fetsel('1', "spip_rubriques", "id_rubrique=$id_parent"))) {
		$champs['id_rubrique'] = $id_parent;

		// si l'tbl_item etait publie
		// et que le demandeur n'est pas admin de la rubrique
		// repasser l'tbl_item en statut 'propose'.
		if ($statut == 'publie'
		AND !autoriser('publierdans', 'rubrique', $id_rubrique))
			$champs['statut'] = 'prop';
	}


	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_items',
				'id_objet' => $id_item
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	// Envoyer les modifs.

	editer_tbl_item_heritage($id_item, $id_rubrique, $statut_ancien, $champs, $calcul_rub);

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_item/$id_item'");

	if ($date) {
		$t = strtotime($date);
		$p = @$GLOBALS['meta']['date_prochain_postdate'];
		if ($t > time() AND (!$p OR ($t < $p))) {
			ecrire_meta('date_prochain_postdate', $t);
		}
	}

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'tbl_items',
				'id_objet' => $id_item
			),
			'data' => $champs
		)
	);

	// Notifications
	if ($notifications = charger_fonction('notifications', 'inc')) {
		$notifications('instituertbl_item', $id_item,
			array('statut' => $statut, 'statut_ancien' => $statut_ancien)
		);
	}

	return ''; // pas d'erreur
}*/

?>
