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
	$liste['tbl_item'] = array('nom'=>4,'chaine_alpha'=>1,/*,'source'=>1,'famille'=>1*/);
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