<?php
/*
 * Plugin xxx
 * (c) 2009 xxx
 * Distribue sous licence GPL
 *
 */

function action_accepter_revision_dist(){
	$securiser_action = charger_fonction('securiser_action','inc');
	$arg = $securiser_action();

	$arg = explode('-',$arg);
	$table = array_shift($arg);
	$trouver_table = charger_fonction('trouver_table','base');
	$desc = $trouver_table($table);
	if (!isset($desc['key']['PRIMARY KEY'])
		OR !$primary = $desc['key']['PRIMARY KEY'])
		die("Erreur $table inconnu");

	$primary = explode(',',$primary);
	$set = array('vu_id_auteur' => $GLOBALS['visiteur_session']['id_auteur'],'vu_date' => 'NOW()');
	
	include_spip('base/abstract_sql');
	// mettre a jour la revision, avec une secu sur double ecriture :
	$where = "vu_id_auteur=0";
	$where .= " AND ".$primary[0]."=".intval($arg[0]);
	if ($arg[1]!=="ALL")
		$where .= " AND ".$primary[1]."=".intval($arg[1]);
	// le premier qui passe est conserve
	sql_updateq($table,$set,$where);

}


?>