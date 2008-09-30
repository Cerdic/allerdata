<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


// {famille_taxo}
function critere_tbl_items_famille_taxo_dist($idb, &$boucles, $crit) {
	$boucle = $boucles[$idb];
	$where = array("'='", "'$boucle->id_table." . "id_type_item'", 2);
	if ($not)
		$where = array("'NOT'",$where);
	$boucle->where[] = $where;

}


?>