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
		'remarques', 'url'
	) as $champ)
		$c[$champ] = _request($champ);

	include_spip('inc/modifier');
	revision_tbl_item($id_item, $c);

	// Modification du(des) parent(s) ?
	$c = array();
	foreach (array(
		'date_item', 'id_parent'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_item($id_item, $c);

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

/**
 * changer le(s) parent(s) d'un item
 *
 * @param int $id_item
 * @param array $c
 * @return unknown
 */
function instituer_tbl_item($id_item, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');
	include_spip('inc/allerdata_arbo');

	$id_parent_actuel = allerdata_les_parents($id_item);
	$champs = array();
	$date = $c['date'];

	// Verifier que le(s) parent(s) demande(s) existe(nt) et sont differents
	// des parents actuels
	if ($id_parent = $c['id_parent']
	AND $id_parent != $id_parent_actuel
	AND ($id_parent = array_map('reset',sql_allfetsel('id_item', "tbl_items", sql_in('id_item',$id_parent))))) {
		$champs['id_parent'] = $id_parent;
	}

	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'tbl_items',
				'table_objet' => 'tbl_items',
				'spip_table_objet' => 'tbl_items',
				'id_objet' => $id_item,
				'type' => 'tbl_item',
				'action'=>'instituer'
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	// Envoyer les modifs.
	if ($champs['id_parent']){
		allerdata_modifier_les_parents($id_item,$champs['id_parent']);
		unset($champs['id_parent']);
	}
	if (count($champs))
		sql_updateq('tbl_items',$champs,'id_item='.intval($id_item));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_item/$id_item'");

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
	return ''; // pas d'erreur
}

/**
 * Versionner la mise a jour d'un item
 * en enregistrant dans tbl_items_versions les changements pour chaque champ
 * associes a un numero de version de cet item
 * fonction appelee sur le pipeline pre_edition
 *
 * @param array $x
 * @return array
 */
function allerdata_versionne_item($x){
	if (
		in_array($x['args']['action'],array('modifier','instituer'))
	  AND $x['args']['table_objet']=='tbl_items'){
		$id_item = $x['args']['id_objet'];
		$maxiter = 5; // on essaye 5 fois maxi
		while (!isset($x['data']['id_version']) AND $maxiter--) {
			if ($row = sql_fetsel('*','tbl_items','id_item='.intval($id_item))
			  AND isset($row['id_version'])){
				$id_version = $row['id_version'];
				
				// si le(s) parent(s) est(sont) modifies
				// charger les parents actuels
				if (isset($x['data']['id_parent'])){
					include_spip('inc/alledata_arbo');
					$row['id_parent'] = allerdata_les_parents($id_item);
				}
				
				// on note les champs modifies
				// ceux que l'on ne voit pas modifies sont enleves de la revision
				// on ne prend aucun risque !
				$diff = array();
				foreach ($row as $k=>$v) {
					if (isset($x['data'][$k])) {
						if ($row[$k]!=$x['data'][$k])
							$diff[$k] = array($row[$k],$x['data'][$k]);
						else 
							unset($x['data'][$k]);
					}
				}
				
				// si aucune modif, on ne versionne pas et on n'enregistre rien
				if (!count($diff)){				
					$x['data'] = array();
					return $x;
				}
				
				$id_versionb = sql_getfetsel('id_version','tbl_items_versions','id_item='.intval($id_item),'','id_version DESC','0,1');
				$id_version = max($id_version,$id_versionb)+1;

				$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
				include_spip('inc/acces');
				$lock = creer_uniqid();
				sql_insertq('tbl_items_versions',array(
					'id_item'=>$id_item,
					'id_version'=>$id_version,
					'id_auteur'=>$id_auteur,
					'commentaires'=>$lock
				));
				if (sql_getfetsel('commentaires','tbl_items_versions','id_item='.intval($id_item).' AND id_version='.intval($id_version))==$lock){
					sql_updateq('tbl_items_versions',array(
					'date'=>'NOW()',
					'commentaires'=>_request('commentaires'),
					'diff'=>serialize($diff),
					),'id_item='.intval($id_item).' AND id_version='.intval($id_version));
					$x['data']['id_version'] = $id_version;
				}
				else
					// on attends 2 secondes que la modif concourante soit finie 
					// pour ressayer une nouvelle fois!
					sleep(2); 
		  }
		}
	  if (!isset($x['data']['id_version']))
			$x['data']['id_version'] = -1;
	}
	return $x;
}

?>