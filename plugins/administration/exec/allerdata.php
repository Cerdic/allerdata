<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

function allerdata_inserer_crayons($out){
	$out = pipeline('affichage_final', "</head>".$out);
	$out = str_replace("</head>","",$out);
	return $out;
}

function exec_allerdata_dist(){
	include_spip("inc/presentation");
	$titre = "Allerdata";
	$commencer_page = charger_fonction('commencer_page','inc');

	$page = _request('page');
	echo $commencer_page("&laquo; $titre &raquo;", "allerdata", "allerdata $page","");
	if (!autoriser('administrer','allerdata')) {
		echo _T('info_acces_interdit');
		echo fin_page();
		exit();
	}
	if ($page==null)
		$page= 'allerdata';

	echo debut_gauche('allerdata',true);
	include_spip('inc/allerdata');
	$barre = array(
		array('titre'=>_T('allerdata:allerdata'),'page'=>'allerdata','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/allerdata-64.gif"),
		array('titre'=>_T("allerdata:comptes"),'page'=>'comptes','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/compte-64.gif",'url'=>generer_url_ecrire('allerdata','page=comptes'))
		);
	if (defined('_DIR_PLUGIN_BIBLIO'))
		$barre[] = array('titre'=>_T("allerdata:bibliographie"),'page'=>'biblios','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/bibliographie-64.png",'url'=>generer_url_ecrire('allerdata','page=biblios'));
	if (defined('_DIR_PLUGIN_COHORTE'))
		$barre[] = array('titre'=>_T("allerdata:cohortes"),'page'=>'cohortes','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/cohorte-64.png",'url'=>generer_url_ecrire('allerdata','page=cohortes'));

	if (defined('_DIR_PLUGIN_ALLER_FTAXO'))
		$barre[] = array('titre'=>_T("allerdata:famille_taxos"),'page'=>'famille_taxos','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/famille_taxo-64.png",'url'=>generer_url_ecrire('allerdata','page=famille_taxos'));
	if (defined('_DIR_PLUGIN_ALLER_FMOL'))
		$barre[] = array('titre'=>_T("allerdata:famille_mols"),'page'=>'famille_mols','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/famille_mol-64.png",'url'=>generer_url_ecrire('allerdata','page=famille_mols'));
	if (defined('_DIR_PLUGIN_ALLER_SOURCES'))
		$barre[] = array('titre'=>_T("allerdata:sources"),'page'=>'sources','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/source-64.png",'url'=>generer_url_ecrire('allerdata','page=sources'));
	if (defined('_DIR_PLUGIN_ALLER_PRODUITS'))
		$barre[] = array('titre'=>_T("allerdata:produits"),'page'=>'produits','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/produit-64.png",'url'=>generer_url_ecrire('allerdata','page=produits'));
	if (defined('_DIR_PLUGIN_ALLER_ALLERGENES'))
		$barre[] = array('titre'=>_T("allerdata:allergenes"),'page'=>'allergenes','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/allergene-64.png",'url'=>generer_url_ecrire('allerdata','page=allergenes'));
	if (defined('_DIR_PLUGIN_MINITEXT'))
		$barre[] = array('titre'=>_T("allerdata:minitextes"),'page'=>'minitextes','icone'=>_DIR_PLUGIN_MINITEXT."img_pack/minitexte-64.png",'url'=>generer_url_ecrire('allerdata','page=minitextes'));
	echo allerdata_barre_nav_gauche($page,$barre);
	echo "<div class='nettoyeur'></div> ";

	echo debut_droite('allerdata',true);
	$message = "";
	if (_request('creer_comptes')){
		include_spip('action/allerdata_creer_comptes');
		list($message,$ids) = allerdata_creer_comptes();				
	}

	switch ($page){
		default:
			$contexte = array('couleur_claire'=>$GLOBALS['couleur_claire'],'couleur_foncee'=>$GLOBALS['couleur_foncee'],'message'=>$message);
			$get = $_GET;
			if (preg_match(',^#[0-9]+$,',$get['recherche']) AND $id = intval(substr($get['recherche'],1))){
				$_id = 'id_item';
				if ($page=='biblios') $_id = 'id_bibliographie';
				if ($page=='cohortes') $_id = 'id_groupes_patient';
				$get[$_id] = $id;
				unset($get['recherche']);
				set_request('recherche','');
				unset($GLOBALS['recherche']);
			}
			if ($page=='cohortes' AND preg_match(',^[0-9]+-$,',$get['recherche'])){
				$get['id_bibliographie'] = intval($get['recherche']);
				unset($get['recherche']);
				set_request('recherche','');
				unset($GLOBALS['recherche']);
			}
			$page = recuperer_fond("prive/$page",array_merge($contexte,$get));
			echo allerdata_inserer_crayons($page);
			break;
	}

	echo fin_gauche();
	echo fin_page();
}

?>