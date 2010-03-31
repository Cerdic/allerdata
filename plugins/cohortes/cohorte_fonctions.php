<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

//
// <BOUCLE(tbl_groupes_patients)>
//
function boucle_tbl_groupes_patients_dist($id_boucle, &$boucles) {
	$boucle = &$boucles[$id_boucle];
	$id_table = $boucle->id_table;
	$mstatut = $id_table .'.statut';

	// Restreindre aux elements publies
	if (!isset($boucle->modificateur['criteres']['statut'])) {
		if (!$GLOBALS['var_preview']) {
			array_unshift($boucle->where,array("'='", "'$mstatut'", "'\\'publie\\''"));
		} else
			array_unshift($boucle->where,array("'IN'", "'$mstatut'", "'(\\'publie\\',\\'prop\\')'"));
	}
	return calculer_boucle($id_boucle, $boucles); 
}

//
// <BOUCLE(tbl_reactions_croisees)>
//
function boucle_tbl_reactions_croisees_dist($id_boucle, &$boucles) {
	$boucle = &$boucles[$id_boucle];
	$id_table = $boucle->id_table;
	$mstatut = $id_table .'.statut';

	// Restreindre aux elements publies
	if (!isset($boucle->modificateur['criteres']['statut'])) {
		if (!$GLOBALS['var_preview']) {
			array_unshift($boucle->where,array("'='", "'$mstatut'", "'\\'publie\\''"));
		} else
			array_unshift($boucle->where,array("'IN'", "'$mstatut'", "'(\\'publie\\',\\'prop\\')'"));
	}
	return calculer_boucle($id_boucle, $boucles); 
}

function critere_tbl_reactions_croisees_produits_dist($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	$_id1 = calculer_liste($crit->param[0], array(), $boucles, $boucles[$idb]->id_parent);
	$_id2 = calculer_liste($crit->param[1], array(), $boucles, $boucles[$idb]->id_parent);

	$boucle->where[] = array("'OR'","'(id_produit1='.intval($_id1).' AND id_produit2='.intval($_id2).')'","'(id_produit1='.intval($_id2).' AND id_produit2='.intval($_id1).')'");
}

?>