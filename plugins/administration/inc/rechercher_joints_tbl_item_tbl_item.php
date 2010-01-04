<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) return;

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
function inc_rechercher_joints_tbl_item_tbl_item_dist($table,$table_liee,$ids_trouves, $serveur='') {
	include_spip('base/abstract_sql');
	$cle_depart = 'id_item';
	$cle_arrivee = 'est_dans_id_item';
	$s = sql_select("$cle_depart,$cle_arrivee", "tbl_est_dans", sql_in($cle_arrivee, $ids_trouves), '','','','',$serveur);
	return array($cle_depart,$cle_arrivee,$s);
}

?>