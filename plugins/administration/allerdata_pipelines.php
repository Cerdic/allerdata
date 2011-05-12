<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Declaration des champs pour la recherche sur la tbl_items
 * utilisee dans le back office
 *
 * @param array $liste
 * @return array
 */
function allerdata_rechercher_liste_des_champs($liste){
	#$liste['tbl_item'] = array('nom'=>8,'nom_anglosaxon'=>8,'nom_court'=>4,'chaine_alpha'=>4,/*,'source'=>1,'famille'=>1*/);
	include_spip('allerdata_fonctions');
	foreach(allerdata_langues() as $l){
		$type = preg_replace(',s$,', '', allerdata_vue('tbl_items',$l));
		$liste[$type] = array('nom'=>8,'nom_anglosaxon'=>8,'nom_court'=>4,'chaine_alpha'=>4,/*,'source'=>1,'famille'=>1*/);
	}
	return $liste;
}

function allerdata_rechercher_liste_des_jointures($liste){
	#$liste['tbl_item'] = array(
	#	'tbl_item' => array('nom'=>2,'nom_anglosaxon'=>2,'nom_court'=>1,'chaine_alpha'=>1,/*,'source'=>1,'famille'=>1*/)
	#	);
	include_spip('allerdata_fonctions');
	foreach(allerdata_langues() as $l){
		$type = preg_replace(',s$,', '', allerdata_vue('tbl_items',$l));
		$liste[$type] = array(
			$type => array('nom'=>2,'nom_anglosaxon'=>2,'nom_court'=>1,'chaine_alpha'=>1,/*,'source'=>1,'famille'=>1*/)
			);
	}

	/**
	 * definition dynamique des fonctions de jointure de recherches
	 */
	foreach(allerdata_langues() as $l){
		$f = preg_replace(',s$,', '', allerdata_vue('tbl_items',$l));
		$f = "inc_rechercher_joints_{$f}_{$f}_dist";

		if (!function_exists($f))
			eval(
			'function '.$f.'($table,$table_liee,$ids_trouves, $serveur="") {
				$g = charger_fonction("rechercher_joints_tbl_item_tbl_item","inc");
				return $g($table,$table_liee,$ids_trouves,$serveur);
			}'
			);
	}

	return $liste;
}


function allerdata_afficher_contenu_objet($flux){
	if ($flux['args']['type']=='auteur'){
		$id_auteur = $flux['args']['id_objet'];
		if ($row = sql_fetsel('pass,pass_clair,alea_actuel','spip_auteurs','id_auteur='.intval($id_auteur))){
			if ($row['pass']==md5($row['alea_actuel'].$row['pass_clair'])){
				$flux['data'].= "<span class='pass_clair'>Mot de passe : <em>".$row['pass_clair']."</em></span>";
			}
		}
	}
	return $flux;
}

function allerdata_header_prive($texte){
	$texte .= "<script type='text/javascript' src='".find_in_path('javascript/jquery.qtip-1.0.0-rc3.js')."'></script>";
	return $texte;
}
?>