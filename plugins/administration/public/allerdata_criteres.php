<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Critere {est_dans xxx} pour la table tbl_items
 * pour trouver les items qui sont dans xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_est_dans_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array('tbl_items','id_est_dans','id_item');
	
	$where = array("'='", "'ed.id_item'", $_id);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}
/**
 * Critere {contient xxx} pour la table tbl_items
 * pour trouver les items qui contiennent xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_contient_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array('tbl_items','id_item');
	
	$where = array("'='", "'ed.id_est_dans'", $_id);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}

/**
 * Critere {famille_taxo} pour la table tbl_items
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_famille_taxo_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 2);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}


/**
 * Critere {source} pour la table tbl_items
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_source_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 4);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}

?>