<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

function biblio_rechercher_journal($nom,$like=false){
	$liste = array();
	if ($like)
		$where = array('nom LIKE '.sql_quote("%".str_replace(' ','%',$nom)."%"));
	else
		$where = array('nom='.sql_quote($nom));
	$res = sql_select('id_journal,nom','tbl_journals',$where,array(),array());
	while ($row = sql_fetch($res))
		$liste[$row['id_journal']] = $row['nom'];

	return $liste;
}

?>