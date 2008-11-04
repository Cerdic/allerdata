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

	echo $commencer_page("&laquo; $titre &raquo;", "allerdata", "allerdata","");
	if (!autoriser('administrer','allerdata')) {
		echo _T('acces_interdit');
		echo fin_page();
		exit();
	}
	$page = _request('page');
	if ($page==null)
		$page= 'allerdata';

	echo debut_gauche('allerdata',true);
	include_spip('inc/allerdata');
	$barre = array(
		array('titre'=>_L('Allerdata'),'page'=>'allerdata','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/allerdata-64.gif"),
		array('titre'=>_L("Comptes"),'page'=>'comptes','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/compte-64.gif",'url'=>generer_url_ecrire('allerdata','page=comptes'))
		);
	if (defined('_DIR_PLUGIN_BIBLIO'))
		$barre[] = array('titre'=>_T("allerdata:bibliographie"),'page'=>'biblios','icone'=>_DIR_PLUGIN_ALLERDATA."img_pack/bibliographie-64.png",'url'=>generer_url_ecrire('allerdata','page=biblios'));

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
	//array('titre'=>_L("Configuration"),'page'=>'cfg','icone'=>_DIR_PLUGIN_BOUTIQUE."img_pack/config-64.png",'url'=>generer_url_ecrire('allerdata','page=cfg')),
	echo allerdata_barre_nav_gauche($page,$barre);

	echo debut_droite('allerdata',true);
	$message = "";
	if (_request('creer_comptes')){
		include_spip('action/allerdata_creer_comptes');
		list($message,$ids) = allerdata_creer_comptes();				
	}

	switch ($page){
		case 'cfg':
			if (!$class || !class_exists($class)) 
				$class = 'cfg';
			$cfg = cfg_charger_classe($class);
			$config = & new $cfg(
				($nom = 'paiement'),
				$nom,
				''
				);
			if ($message = lire_meta('cfg_message_'.$GLOBALS['auteur_session']['id_auteur'])) {
				include_spip('inc/meta');
				effacer_meta('cfg_message_'.$GLOBALS['auteur_session']['id_auteur']);
				ecrire_metas();
				$config->message = $message;
			}
			$config->traiter();
			echo debut_cadre_trait_couleur('',true);
			echo $config->formulaire();
			echo fin_cadre_trait_couleur(true);
			break;
		default:
			$contexte = array('couleur_claire'=>$GLOBALS['couleur_claire'],'couleur_foncee'=>$GLOBALS['couleur_foncee'],'message'=>$message);
			$get = $_GET;
			if (is_numeric($get['recherche']) AND intval($get['recherche'])){
				$get['id_item'] = intval($get['recherche']);
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