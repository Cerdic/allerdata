<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

define('_URL_PUBMED','http://www.ncbi.nlm.nih.gov/pubmed/');

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
	// raz :p
	$GLOBALS['doublons_bibliographies_inclus']=array();
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

//@define('_REGLE_AUTEURS',";^([\w]+(\s+[A-Z]{1,3}(\s+(Jr|Sr))?)?[.]\s*)+;u");
@define('_REGLE_AUTEURS',";^([^.]+?[\s]+[A-Z\-0-9]{1,4}[.]\s*)+$;u");
@define('_REGLE_AUTEURS_SPLIT',";[^.]+?[\s]+[A-Z\-0-9]{1,4}[.]\s*;u");
function biblio_extrait_auteurs($auteurs){
	$auteurs = trim($auteurs);
	$auteurs = rtrim($auteurs,'.').'.'; // s'assurer qu'on a un point a la fin
	if (preg_match(',et al.$,',$auteurs))
		return false;
	
	if (!preg_match(_REGLE_AUTEURS,$auteurs,$r))
		return false;

	preg_match_all(_REGLE_AUTEURS_SPLIT,$auteurs,$r);
	return reset($r);
}

function biblio_citer_auteurs($auteurs){
	if (!$liste = biblio_extrait_auteurs($auteurs))
		return $auteurs;
	$cite = array();
	$max = 6;
	while (count($liste) AND $max--)
		$cite [] = rtrim(trim(array_shift($liste)),'.');
	$cite = implode(', ',$cite);
	if (count($liste))
		$cite .= " et al";
	$cite .= ".";
	return $cite;
}

function biblio_trouver_sembables($auteurs,$titre,$autre_media,$id_journal,$annee,$volume,$numero,$supplement,$premiere_page){
	$liste = array();
	// trouver les biblios avec auteur semblable
	// si au moins 2 auteurs ou non analysable
	if ($auteurs AND (!($les_auteurs=biblio_extrait_auteurs($auteurs)) OR count($les_auteurs)>1)){
		$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies','auteurs='.sql_quote($auteurs))));
	}
	if (strlen(trim($titre)))
		// biblio avec le meme titre ?
		$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies','titre='.sql_quote($titre))));
		
	// biblio avec le meme autre_media ?
	if (strlen(trim($autre_media)))
		$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies','(id_journal=0) AND autre_media='.sql_quote($autre_media))));

	if ($annee AND $volume AND $premiere_page){
		if (!$numero AND !$supplement){
			$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies',
			  '(numero=\'\' OR numero IS NULL) AND (supplement=\'\' OR supplement IS NULL) 
			  AND concat(id_journal,\'-\',annee,\'-\',volume,\'-\',premiere_page)='.sql_quote("$id_journal-$annee-$volume-$premiere_page"))));
		}
		elseif (!$supplement){
			$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies',
			  '(supplement=\'\' OR supplement IS NULL) 
			  AND concat(id_journal,\'-\',annee,\'-\',volume,\'-\',numero,\'-\',premiere_page)='.sql_quote("$id_journal-$annee-$volume-$numero-$premiere_page"))));
		}
		elseif (!$numero){
			$liste = array_merge($liste,array_map('reset',sql_allfetsel('id_bibliographie','tbl_bibliographies',
			  '(numero=\'\' OR numero IS NULL) 
			  AND concat(id_journal,\'-\',annee,\'-\',volume,\'-\',supplement,\'-\',premiere_page)='.sql_quote("$id_journal-$annee-$volume-$supplement-$premiere_page"))));
		}
	}
	sort($liste);
	return $liste;
}

function biblio_citer($auteurs,$titre,$autre_media,$journal,$annee,$volume,$numero,$supplement,$premiere_page,$derniere_page){
	$auteurs = biblio_citer_auteurs($auteurs);
	if (!strlen(trim($journal)))
		return "$auteurs $titre. $autre_media";
	
	if (preg_match(',ahead,',$autre_media))
		return "$auteurs $titre. $autre_media";

	if (!strlen(trim($supplement)))
		return 
		  "$auteurs $titre. $journal $annee;$volume" .
		  (strlen(trim($numero))?"($numero)":"") .
		  ":$premiere_page" .
		  (strlen(trim($derniere_page))?"-$derniere_page":"");

	return 
	  "$auteurs $titre. $journal $annee;$volume" .
	  (strlen(trim($supplement))?"($supplement)":"") .
	  ":$premiere_page" .(strlen(trim($derniere_page))?"-$derniere_page":"");
}

?>