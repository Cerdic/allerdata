<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

include_spip('base/serial');
$GLOBALS['tables_principales']['spip_auteurs']['pass_clair']="tinytext DEFAULT '' NOT NULL";

/****/
/* Compat fonctions 2.1 */

function icone_inline($texte, $lien, $fond, $fonction="", $align="", $ajax=false, $javascript=''){
	return icone_base($lien,$texte,$fond,$fonction,"verticale $align",$javascript);
}



/**
 * Lister les langues traduites en base
 * un champ par langue, suffixe par la langue :
 * texte_fr, texte_en
 * @return array
 */
function allerdata_langues(){	return array('fr','en'); }

/**
 * Donner le nom de la vue a utiliser en fonction du nom de la table et de la langue
 * @param string $table
 * @param string $langue
 * @return string
 */
function allerdata_vue($table,$langue=null){
	if (is_null($langue))
		$langue = $GLOBALS['spip_lang'];
	if (in_array($langue,allerdata_langues()))
		return $langue ."_". $table;
	return "fr_".$table;
}

/**
 * Lister les declinaisons traduites d'un champ
 * nom => nom_fr, nom_en
 * @param string $champ
 * @return array
 */
function allerdata_liste_champs_trad($champ){
	$liste = array();
	foreach(allerdata_langues() as $l)
		$liste[$l] = $champ . '_' .$l;
	return $liste;
}
/**
 * Compiler un champ traduit :
 * - requeter chaque langue
 * - recuperer la valeur au calcul, en fonction de la langue courante
 *
 * @param string $champ
 * @param object $p
 * @return string
 */
function allerdata_champ_sql_trad($champ,$p){
	$code = 'array(';
	foreach(allerdata_liste_champs_trad($champ) as $l=>$c)
		$code .= "'$l'=> &".champ_sql($c, $p).",";
	$code .= ")";
	$code = "allerdata_traduit_champ(\$GLOBALS['spip_lang'],$code)";
	return $code;
}

/**
 * Recuperer la traduction d'un champ en fonction de la langue courante
 * Si la langue courante n'est pas traduite, passer par la trad a la volee sur le champ francais
 * qui est la reference
 *
 * @param string $lang
 * @param array $trads
 * @return string
 */
function allerdata_traduit_champ($lang,$trads){
	// si langue connue et traduite, la renvoyer
	if (isset($trads[$lang]) AND strlen($trads[$lang]))
		return $trads[$lang];

	// sinon renvoyer la langue par defaut traduite a la volee
	return aT(reset($trads),$lang);
}

function allerdata_multiplexe_erreurs_trad($champ,&$erreurs){
	$champs = allerdata_liste_champs_trad($champ);
	foreach($champs as $l=>$c){
		if (!isset($erreurs[$champ]) AND isset($erreurs[$c]))
			$erreurs[$champ] = $erreurs[$c];
	}
}

if (!defined('_ALLERDATA_DEBUG_LANG')) define('_ALLERDATA_DEBUG_LANG',true);
/**
 * Fonction de traduction a la volee pour les contenus non traduits
 * avec mise en cache
 * @param string $texte
 * @param string $langue_cible
 * @param string $lang_ref
 * @return string
 */
function aT($texte, $langue_cible, $lang_ref='fr'){
	if ($langue_cible==$lang_ref)
		return $texte;
	// TODO : google translate avec cache ici
	#include_spip('inc/translate');
	#if ($t = translate($texte, $lang_ref, $langue_cible))
	#	return $t;

	// sinon, non traduit
	if (_ALLERDATA_DEBUG_LANG AND strlen($texte))
		return "<span style='color:red'>[$langue_cible]$texte</span>";

	return $texte;
}

/**
 * traduction des des champs en fonction de la langue :
 * Dans une boucle tbl_items, le champ #c est traduit en c_fr, c_en...
 * on generer une fonction de surcharge par champ concerne
 * @param object $p
 * @return
 */
foreach(array('nom','autre_nom','nom_complet','nom_court','chaine_alpha','representatif','fonction_classification') as $c){
	$f = "balise_".strtoupper($c)."_dist";
	eval(
'function '.$f.'($p){
	if ($p->boucles[$p->id_boucle]->type_requete=="tbl_items") $p->code = allerdata_champ_sql_trad("'.$c.'",$p);
	else $p->code = champ_sql("'.$c.'", $p);
	return $p;
}');
}


/**
 * Regarder si un item a des enfants designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les enfants presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_sans_enfant($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_item',"i.statut='publie' AND ed.est_dans_id_item=".intval($id_item)." AND directement_contenu=1");
}
/**
 * Regarder si un item a des parents designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les parents presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_orphelin($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.est_dans_id_item','ed.id_item='.intval($id_item)." AND directement_contenu=1");
}

/**
 * Enter description here...
 *
 * @param unknown_type $id_type_item
 * @param unknown_type $plur
 * @return unknown
 */
function allerdata_type_item($id_type_item,$plur=''){
	switch ($id_type_item){
		case 2:
			return 'famille_taxo'.$plur;
			break;
		case 1:
		case 3:
		case 5:
		case 13:
		case 23:
		case 25:
			return 'produit'.$plur;
			break;
		case 11:
			return 'testcomposite'.$plur;
			break;
		case 4:
			return 'source'.$plur;
			break;
		case 6:
			return 'famille_mol'.$plur;
			break;
		case 7:
		case 8:
		case 9:
		case 10:
		case 18:
			return 'allergene'.$plur;
			break;
	}
	return "type_item_$id_type_item";	
}
/**
 * Enter description here...
 *
 * @param unknown_type $type
 * @param unknown_type $tous
 * @return unknown
 */
function allerdata_id_type_item($type,$tous=false){
	switch ($type){
		case 'famille_mol':
			return array(6);
			break;
		case 'famille_taxo':
			return array(2);
			break;
		case 'source':
			return array(4);
			break;
		case 'categorie_produit':
				return array(1);
			break;
		case 'produit_generique':
			if ($tous)
				return array(3,5,13,23,25);
			else
				return array(3,5);
			break;
		case 'produit':
			if ($tous)
				return array(/*1,*/3,5,13,23,25); // ticket #243
			else
				return array(/*1,*/3,5); // ticket #243
			break;
		case 'produit_et_categorie':
			if ($tous)
				return array(1,3,5,13,23,25);
			else
				return array(1,3,5);
			break;
		case 'produit_simple':
			if ($tous)
				return array(5,25);
			else
				return array(5);
			break;
		case 'produit_en_attente':
			return array(13,23,25);
			break;
		case 'allergene':
			if ($tous)
				return array(7,8,9,10,18);
			else
				return array(7,8,9,10);
			break;
		case 'allergene_recombinant':
				return array(9);
			break;
		case 'allergene_en_attente':
			return array(18);
			break;
	}
	return array();	
}

/**
 * Retrouver tous les items racine ascendants d'un item
 *
 * @param int $id_item
 * @return array
 */
function allerdata_item_racines($id_item){
	// trouver les parents
	if ($parents = sql_allfetsel('i.id_item','tbl_est_dans AS ed JOIN tbl_items AS i ON i.id_item=ed.est_dans_id_item','ed.id_item='.intval($id_item).' AND ed.directement_contenu=1')){
		$parents = array_map('reset',$parents);
		$parents = array_map('allerdata_item_racines',$parents);
		return call_user_func_array('array_merge',$parents);
	}
	else
		// c'est une racine !
		return array($id_item);
}

/**
 * exporter un champ pour l'affichage du diff de version
 *
 * @param unknown_type $t
 * @return unknown
 */
function allerdata_field2string($t){
	if (is_array($t))
		sort($t);
	return is_array($t) ? implode(', ',$t) : $t;
}

/**
 * Afficher de facon intelligible les revisions champ par champ pour une version donnee
 *
 * @param array $diff
 * @return string
 */
function allerdata_affiche_revision($diff){
	if (!is_array($diff)) $diff = unserialize($diff);
	$res = "";
	if (is_array($diff)){
		include_spip('inc/revisions');
		include_spip('inc/diff');
		$i=0;
		foreach($diff as $k => $avap){
			$diff = new Diff(new DiffTexte);
			$avant = $apres = "";
			if (is_array($avap)){
				list($avant,$apres) = $avap;
			}
			$o = preparer_diff(allerdata_field2string($avant));
			$n = preparer_diff(allerdata_field2string($apres));
			$diff = afficher_diff($diff->comparer($n,$o));
				$res .= "<tr class='row_".(($i++&1)?'even':'odd')."'>"
				. "<td class='champ'><b>$k</b></td>"
				#. "<td>$avant</td>"
				#. "<td>$apres</td>"
				. "<td class='diff'>$diff</td>"
				. "</tr>";
		}
	}
	return $res ? "<table class='spip'><thead><tr class='row_first'><th class='champ'>Champ</th><th class='diff'>Modification</th></tr></thead><tbody>$res</tbody></table>":"";
}

$GLOBALS['liste_des_etats_items'] = array(
		'allerdata:item_statut_propose_evaluation' => 'prop',
		'texte_statut_publie' => 'publie',
		'texte_statut_poubelle' => 'poubelle',
	);
function allerdata_selecteur_statut($statut,$name,$id=''){
	$etats = $GLOBALS['liste_des_etats_items'];

	$res = "<select name='$name'"
	. ($id?" id='$id'":"")
	. ">";
	foreach($etats as $affiche => $s){
		if (in_array($s,array('prop','publie','poubelle'))){
			$selected = "";
			$res .= "<option value='$s'"
			.(($s==$statut)?' selected="selected"':'')
			. ">"
			._T($affiche)
			."</option>";
		}
	}
	$res .= "</select>";
	return $res;
}

function allerdata_puce_statut($statut){
	include_spip('inc/puce_statut');
	$etats = $GLOBALS['liste_des_etats_items'];

	$res .=
	  "<ul id='instituer_article-$id_article' class='instituer_article instituer'>" 
	  . "<li>" 
	  ."<ul>";
	
	foreach($etats as $affiche => $s){
		$puce = puce_statut($s) . _T($affiche);
		if ($s==$statut){
			$class=' selected';
			$res .= "<li class='$s $class'>$puce</li>";
		}
	}

	$res .= "</ul></li></ul>";
  
	return $res;
}

function allerdata_numero_version($id_version){
	if ($id_version==0)
		return "<strong>"._T('allerdata:version_creation')."</strong>";
	else
		return _T('allerdata:version_modification')."<strong>#$id_version</strong>";
}


//
// <BOUCLE(tbl_items)>
//
function boucle_tbl_items_dist($id_boucle, &$boucles) {
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

?>