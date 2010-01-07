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

	// tbl_items_versions-#ID_ITEM:#ID_VERSION
	// tbl_items_versions-#ID_ITEM:#ID_VERSION-#ID_ITEM:#ID_VERSION-#ID_ITEM:#ID_VERSION
	// tbl_items_versions-#ID_ITEM:ALL
	// tbl_items_versions-ALL:ALL
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
	foreach($arg as $item){
		list($id,$version) = explode(':',$item);
		// mettre a jour la revision, avec une secu sur double ecriture :
		$where = "vu_id_auteur=0";

		if ($id!=="ALL"){
			$where .= " AND ".$primary[0]."=".intval($id);
			// toutes les revisions d'un objet
			if ($version!=="ALL")
				$where .= " AND ".$primary[1]."=".intval($version);
			// le premier qui passe est conserve
		}
		sql_updateq($table,$set,$where);
	}

}


?>