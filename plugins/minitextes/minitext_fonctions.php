<?php
/*
 * Plugin xxx
 * (c) 2009 xxx
 * Distribue sous licence GPL
 *
 */



function critere_tbl_minitextes_derniere_version_dist($idb, &$boucles, $crit){
	$boucle = $boucles[$idb];
	$boucle->from['version'] = 'tbl_minitextes_versions';
	$boucle->join['version'] = array("'tbl_minitextes'","'id_minitexte'","'id_minitexte'","'version.id_version=tbl_minitextes.id_version'");
	$boucle->from_type['version'] = 'LEFT';

	$boucle->select['date_modif_version'] = 'version.date AS date_modif_version';
	$boucle->select['id_auteur_version'] = 'version.id_auteur AS id_auteur_version';
}


?>