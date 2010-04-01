<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Critere {est_dans xxx} pour la table tbl_items
 * pour trouver tous les items qui sont dans xxx y compris xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_est_dans_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array('tbl_items','id_item');
	
	$where = "is_array(\$inid=($_id))?sql_in('ed.est_dans_id_item',\$inid):array('=', 'ed.est_dans_id_item', intval(\$inid))";
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}

/**
 * Critere {est_directement_dans xxx} pour la table tbl_items
 * pour trouver les items qui sont directement dans xxx sans xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_est_directement_dans_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array('tbl_items','id_item');
	
	$where = array("'AND'",array("'='", "'ed.est_dans_id_item'", "intval(".$_id.")"),array("'='", "'ed.directement_contenu'", "1"));
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}


/**
 * Critere {contient xxx} pour la table tbl_items
 * pour trouver tous les items qui contiennent xxx y compris xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_contient_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array("'tbl_items'","'est_dans_id_item'","'id_item'");
	
	$where = array("'='", "'ed.id_item'", $_id);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}

/**
 * Critere {contient_directement xxx} pour la table tbl_items
 * pour trouver les items qui contiennent directement xxx, sans xxx
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_contient_directement_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$_id = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);

	$boucle->from['ed'] = 'tbl_est_dans';
	$boucle->join['ed'] = array('tbl_items','est_dans_id_item','id_item');
	
	$where = array("'AND'",array("'='", "'ed.id_item'", $_id),array("'='", "'ed.directement_contenu'", "1"));
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}


/**
 * Critere {type_item xxx} pour la table tbl_items
 * permet de faire {type_item source}, {type_item famille_mol} ...
 * et d'utiliser une variable pour le type
 * 
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_type_item_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$_type = calculer_liste($crit->param[0], array(), $boucles, $boucle->id_parent);
	$boucle->where[] = "sql_in('".$boucle->id_table.".id_type_item',allerdata_id_type_item($_type"
	  .($boucle->modificateur['tout']?",true":"")
	  .")".($not?",'NOT'":"").")";
}

function critere_tbl_types_items_type_item_dist($idb, &$boucles, $crit) {
	critere_tbl_items_type_item_dist($idb, $boucles, $crit);
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
	$not = $crit->not;
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 2);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}

/**
 * Critere {famille_mol} pour la table tbl_items
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_famille_mol_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 6);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}


/**
 * Critere {produit} pour la table tbl_items
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_produit_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$where = array("'IN'", "'$boucle->id_table." . "id_type_item'", "'(3,5,23,25,13)'");
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;
}


/**
 * Critere {allergene} pour la table tbl_items
 *
 * @param string $idb
 * @param array $boucles
 * @param array $crit
 */
function critere_tbl_items_allergene_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$not = $crit->not;
	$where = array("'IN'", "'$boucle->id_table." . "id_type_item'", "'(7,8,9,10,18)'");
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
	$not = $crit->not;
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 4);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}

?>