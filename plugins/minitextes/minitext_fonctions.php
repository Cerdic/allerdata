<?php
/*
 * Plugin xxx
 * (c) 2009 xxx
 * Distribue sous licence GPL
 *
 */



function critere_tbl_minitextes_derniere_version_dist($idb, &$boucles, $crit){
	$boucle = $boucles[$idb];
	$boucle->from['version'] = 'tbl_minitextes_versions';
	$boucle->join['version'] = array("'tbl_minitextes'","'id_minitexte'","'id_minitexte'","'version.id_version=tbl_minitextes.id_version'");
	$boucle->from_type['version'] = 'LEFT';

	$boucle->select['date_modif_version'] = 'version.date AS date_modif_version';
	$boucle->select['id_auteur_version'] = 'version.id_auteur AS id_auteur_version';
}


function minitext_extraire_titre($texte){
	if (preg_match(",^\s*<h([1-6])[^>]*>(.*)</h\\1>,Uims",$texte,$regs))
		return textebrut($regs[2]);
	return couper($texte,60);
}

function boucle_tbl_minitextes_dist($id_boucle, &$boucles) {
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


function critere_tbl_minitextes_rc_dist($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	$_id1 = calculer_liste($crit->param[0], array(), $boucles, $boucles[$idb]->id_parent);
	$_id2 = calculer_liste($crit->param[1], array(), $boucles, $boucles[$idb]->id_parent);

	$boucle->join['rc']=array("'".$boucle->id_table."'","'".$boucle->primary."'");
	$boucle->from['rc']='tbl_minitextes_items';

	$boucle->where[] = array("'OR'","'(rc.id_item_1='.intval($_id1).' AND rc.id_item_2='.intval($_id2).')'","'(rc.id_item_1='.intval($_id2).' AND rc.id_item_2='.intval($_id1).')'");
}


?>