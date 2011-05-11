<?php
/*
 * Plugin xxx
 * (c) 2009 xxx
 * Distribue sous licence GPL
 *
 */

/**
 * Dans une boucle minitextes, le champ #TEXTE est traduit en texte_fr, texte_en...
 * @param object $p
 * @return
 */
function balise_TEXTE_dist($p){
	if ($p->boucles[$p->id_boucle]->type_requete=='tbl_minitextes')
		$p->code = allerdata_champ_sql_trad('texte',$p);
	else
		$p->code = champ_sql('texte', $p);
	return $p;
}


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


function minitext_find_rc($p1,$p2){
	$texte = 'texte_'.$GLOBALS['spip_lang'];
	if ($row = sql_fetsel("M.id_minitexte,M.$texte as texte",
						"tbl_minitextes AS M JOIN tbl_minitextes_items as L ON L.id_minitexte=M.id_minitexte",
						"M.statut='publie' AND ((L.id_item_1=".intval($p1)." AND L.id_item_2=".intval($p2).") OR (L.id_item_1=".intval($p2)." AND L.id_item_2=".intval($p1)."))"))
		return $row;

	// chercher parmis les parents
	include_spip('inc/allerdata_arbo');
	$p1s = allerdata_les_parents($p1, "produit_et_categorie");
	$p2s = allerdata_les_parents($p2, "produit_et_categorie");
	if ($row= sql_fetsel("M.id_minitexte,M.$texte as texte",
					"tbl_minitextes AS M JOIN tbl_minitextes_items as L ON L.id_minitexte=M.id_minitexte",
					"M.statut='publie' AND ((".sql_in('L.id_item_1',$p1s)." AND ".sql_in('L.id_item_2',$p2s)
					.") OR (".sql_in('L.id_item_1',$p2s)." AND ".sql_in('L.id_item_2',$p1s)."))"))
		return $row;

	return false;
}
?>