<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

function biblio_rechercher_journal($nom,$like=false){
	$liste = array();
	if ($like)
		$where = array('nom LIKE '.sql_quote("%".str_replace(' ','%',$nom)."%"));
	else
		$where = array('nom='.sql_quote($nom));
	$res = sql_select('id_journal,nom','tbl_journals',$where,array(),array());
	while ($row = sql_fetch($res))
		$liste[$row['id_journal']] = $row['nom'];

	return $liste;
}


function marquer_liens_biblios($champs,$id,$type,$id_table_objet,$table_objet,$spip_table_objet, $desc=array(), $serveur=''){
	if (!isset($champs['texte']) AND !isset($champs['chapo'])) return;
	if (!$desc){
		$trouver_table = charger_fonction('trouver_table', 'base');
		$desc = $trouver_table($table_objet, $serveur);
	}
	$load = "";

	// charger le champ manquant en cas de modif partielle de l'objet
	// seulement si le champ existe dans la table demande
	if (!isset($champs['texte']) && isset($desc['field']['texte'])) $load = 'texte';
	if (!isset($champs['chapo']) && isset($desc['field']['chapo'])) $load = 'chapo';
	if ($load){
		$champs[$load] = "";
		$row = sql_fetsel($load, $spip_table_objet, "$id_table_objet=".sql_quote($id));
		if ($row AND isset($row[$load]))
			$champs[$load] = $row[$load];
	}
	include_spip('inc/texte');
	include_spip('inc/lien');
	include_spip('base/abstract_sql');
	$texte = traiter_modeles($champs['chapo'].$champs['texte'],array('bibliographies'=>array('biblio'))); // detecter les doublons bibliographies
	sql_delete("spip_bibliographies_$table_objet", "$id_table_objet=$id");
	if (count($GLOBALS['doublons_bibliographies_inclus'])){
		// on repasse par une requete sur tbl_bibliographies pour verifier que les biblio existent bien !
		$in_liste = sql_in('id_bibliographie',
			$GLOBALS['doublons_bibliographies_inclus']);
		$res = sql_select("id_bibliographie", "tbl_bibliographies", $in_liste);
		while ($row = sql_fetch($res)) {
			// Creer le lien s'il n'existe pas deja
			sql_insertq("spip_bibliographies_$table_objet", array($id_table_objet=>$id, 'id_bibliographie' => $row['id_bibliographie']));
		}
	}
}
?>