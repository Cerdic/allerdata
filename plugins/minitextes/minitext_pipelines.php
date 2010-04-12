<?php
/*
 * Plugin Minitext
 * (c) 2010 Cedric MORIN Yterium
 * pour Allerdata
 * Distribue sous licence GPL
 *
 */


/**
 * Declaration des champs pour la recherche sur la tbl_minitextes
 * utilisee dans le back office
 *
 * @param array $liste
 * @return array
 */
function minitext_rechercher_liste_des_champs($liste){
	$liste['tbl_minitexte'] = array();
	return $liste;
}

function minitext_rechercher_liste_des_jointures($liste){
	$liste['tbl_minitexte'] = array(
	'tbl_item' => array('chaine_alpha'=>2,'nom'=>1),
	);
	return $liste;
}



/**
 * Lister les id de la $table depart lies aux resultats trouves
 * enumeres par $ids_trouves
 * dans la $table_liee arrivee, sur le $serveur
 *
 *
 * @param <type> $table
 *   table de depart dans laquelle on fait la recherche
 * @param <type> $table_liee
 *   table liee dans laquelle une recherche a aussi ete faite
 * @param <type> $ids_trouves
 *   passe en reference pour des raison de perfo
 * @param <type> $serveur
 *   serveur de base utilise pour la recherche
 * @return array
 *   retourne la cle dans la table de depart, la cle dans la table d'arrivee, et la liste des objets lies
 */
function inc_rechercher_joints_tbl_minitexte_tbl_item_dist($table,$table_liee,$ids_trouves, $serveur='') {
	include_spip('base/abstract_sql');
	$cle_depart = 'id_minitexte';
	$cle_arrivee = 'id_item';
	$s = sql_allfetsel("$cle_depart,$cle_arrivee", "tbl_items", sql_in($cle_arrivee, $ids_trouves), '','','','',$serveur);
	$s = array_merge($s,sql_allfetsel("$cle_depart,id_item_1 AS $cle_arrivee", "tbl_minitextes_items", sql_in('id_item_1', $ids_trouves), '','','','',$serveur));
	$s = array_merge($s,sql_allfetsel("$cle_depart,id_item_2 AS $cle_arrivee", "tbl_minitextes_items", sql_in('id_item_2', $ids_trouves), '','','','',$serveur));

	return array($cle_depart,$cle_arrivee,$s);
}


?>