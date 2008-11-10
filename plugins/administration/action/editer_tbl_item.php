<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

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
		'id_type_item', 'nom', 'source', 'famille', 'autre_nom', 'nom_complet', 'chaine_alpha', 
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
		'date_item', 'id_parent', 'statut'
	) as $champ)
		$c[$champ] = _request($champ);
	$err .= instituer_tbl_item($id_item, $c);

	// Un lien de trad a prendre en compte
	#$err .= tbl_item_referent($id_item, array('lier' => _request('lier')));

	return $err;
}

function insert_tbl_item($id_type_item) {

	$max_id = array('produit'=>9999,'allergene'=>19999,'source'=>29999,'famille_taxo'=>39999,1=>59999,'famille_mol'=>49999);
	
	$set = array(
		'id_type_item' => $id_type_item,
		'id_version' => -2, // indiquer une creation
		'date_item' => 'NOW()',
		'statut'=>'publie', // pour le moment
	);
	// trouver un id coherent !
	// recuperer la borne max fonction de id_type ou du type au sens large :
	$max = 0;
	include_spip('allerdata_fonctions');
	if (isset($max_id[$id_type_item])){
		$max = $max_id[$id_type_item];
		$where = 'id_type_item='.intval($id_type_item);
	}
	else {
		$max = $max_id[$t=allerdata_type_item($id_type_item)];
		$where = sql_in('id_type_item',allerdata_id_type_item($t,true));
	}
		
	// en essayant 3 fois chaque methode en cas d'insertion concourante
	$maxiter = 3;
	while ($max AND $maxiter--) {
		// on prend un id correspondant a max(id)+1 du meme type
		$id_item = sql_getfetsel('id_item','tbl_items',$where . " AND id_item<".intval($max),'','id_item DESC','0,1');
		if ($id_item
		AND $id_item = sql_insertq("tbl_items", array_merge($set,array('id_item'=>$id_item+1))))
			return $id_item;
	}

	// sinon faire une insertion a la fin, avec l'autoincrement
	$id_item = sql_insertq("tbl_items", $set);

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
	AND $id_parent != $id_parent_actuel){
		$id_parent = array_map('reset',sql_allfetsel('id_item', "tbl_items", sql_in('id_item',$id_parent)));
		$champs['id_parent'] = $id_parent;
	}
	
	$statut_ancien = sql_getfetsel('statut','tbl_items','id_item='.intval($id_item));
	if ($statut = $c['statut']
	 AND $statut!=$statut_ancien)
		$champs['statut'] = $statut;

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
				'table_objet' => 'tbl_items',
				'spip_table_objet' => 'tbl_items',
				'id_objet' => $id_item,
				'type' => 'tbl_item',
				'action'=>'instituer'
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
	  AND in_array($x['args']['table_objet'],array('tbl_items','tbl_bibliographies','tbl_groupes_patients'))){
	  
	  $_table = $x['args']['table_objet'];
	  $_id_table = 'id_'.rtrim(substr($_table,4),'s');
		$id = $x['args']['id_objet'];
		$maxiter = 5; // on essaye 5 fois maxi
		while (!isset($x['data']['id_version']) AND $maxiter--) {
			if ($row = sql_fetsel('*',$_table,"$_id_table=".intval($id))
			  AND isset($row['id_version'])){
				$id_version = $row['id_version'];
				
				// si le(s) parent(s) est(sont) modifies
				// charger les parents actuels
				if ($_table=='tbl_items' AND isset($x['data']['id_parent'])){
					include_spip('inc/alledata_arbo');
					$row['id_parent'] = allerdata_les_parents($id);
				}
				
				// on note les champs modifies
				// ceux que l'on ne voit pas modifies sont enleves de la revision
				// on ne prend aucun risque !
				$diff = array();
				foreach ($row as $k=>$v) {
					if (isset($x['data'][$k])) {
						$change = $row[$k]!=$x['data'][$k];
						if (is_array($row[$k]) AND is_array($x['data'][$k]))
							$change = count(array_diff($row[$k],$x['data'][$k])) + count(array_diff($x['data'][$k],$row[$k]));
						if ($change)
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
				
				$commentaires = _request('commentaires');
				if ($id_version==-2){ // creation
					$commentaires = "Creation de l'$_id_table\n$commentaires";
					$id_version = -1; // creation=version 0
				}
				if ($id_versionb = sql_getfetsel('id_version',$_table.'_versions',"$_id_table=".intval($id),'','id_version DESC','0,1'))
					$id_version = max($id_version,$id_versionb)+1;
				else 
					$id_version++;

				$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
				include_spip('inc/acces');
				$lock = creer_uniqid();
				sql_insertq($_table.'_versions',array(
					$_id_table=>$id,
					'id_version'=>$id_version,
					'id_auteur'=>$id_auteur,
					'commentaires'=>$lock
				));
				if (sql_getfetsel('commentaires',$_table.'_versions',"$_id_table=".intval($id).' AND id_version='.intval($id_version))==$lock){
					sql_updateq($_table.'_versions',array(
					'date'=>'NOW()',
					'commentaires'=>$commentaires,
					'diff'=>serialize($diff),
					),"$_id_table=".intval($id).' AND id_version='.intval($id_version));
					$x['data']['id_version'] = $id_version;
				}
				else
					// on attends 1 seconde que la modif concourante soit finie 
					// pour ressayer une nouvelle fois!
					sleep(1); 
		  }
		}
	  if (!isset($x['data']['id_version']))
			$x['data']['id_version'] = 0;
	}
	return $x;
}

?>