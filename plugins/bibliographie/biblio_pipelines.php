<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


/**
 * Autorisation de supprimer une reference biblio
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_bibliographie_supprimer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// verifier que le statut n'est pas deja poubelle
	if ($statut=$options['statut']
	 OR $statut = sql_getfetsel('statut','tbl_bibliographies','id_bibliographie='.intval($id)))
		if ($statut=='poubelle') return false;
	// interdit si la biblio est utilisee dans un article
	if (sql_countsel('spip_bibliographies_articles','id_bibliographie='.intval($id)))
		return false;
	// interdit si la biblio est utilisee par un groupe de patient publie
	if (sql_countsel('tbl_groupes_patients','id_bibliographie='.intval($id)))
		return false;
	return true;
}


/**
 * Autorisation de changer de statut une reference biblio
 * necessite d'etre admin, et qu'elle soit inutilisee
 *
 * @param unknown_type $faire
 * @param unknown_type $quoi
 * @param unknown_type $id
 * @param unknown_type $qui
 * @param unknown_type $options
 * @return unknown
 */
function autoriser_bibliographie_instituer($faire,$quoi,$id,$qui,$options){
	if (!$qui['statut']=='0minirezo') return false;
	if ($qui['restreint']) return false;
	include_spip('base/abstract_sql');
	// si le statut est deja poubelle, on autorise !
	$statut = sql_getfetsel('statut','tbl_bibliographies','id_bibliographie='.intval($id));
	if ($statut=='poubelle') return true;
	return autoriser('supprimer','bibliographie',$id,$qui,array('statut'=>$statut));
}




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