<?php
/*
 * Plugin Bibliographie / Admin des biblios
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
function biblio_rechercher_liste_des_champs($liste){
	$liste['tbl_bibliographie'] = array('titre'=>2,'auteurs'=>2,'citation'=>2,'autre_media'=>1,'abstract'=>1);
	return $liste;
}

function biblio_rechercher_liste_des_jointures($liste){
	$liste['tbl_bibliographie'] = array(
	'tbl_journal' => array('nom'=>2),
	);
	return $liste;
}



/**
 * pipeline affiche_droite
 * pour afficher la liste des references utilisees dans un article
 *
 * @param unknown_type $flux
 */
function biblio_affiche_droite($flux){
	if ($flux['args']['exec']=='articles'
	  AND $id_article= $flux['args']['id_article']){
		$flux['data'] .= recuperer_fond('prive/boite_references',array_merge($_GET,array('id_article'=>$id_article)));
	}
	return $flux;
}

?>