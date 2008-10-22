<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_supprimer_item_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	if ($id_item = intval($arg)
	  AND $dump = sql_fetsel('*','tbl_items','id_item='.intval($id_item))){
	  //$nom = $dump['nom'];
	  //$dump = var_export($dump,true);
		include_spip('inc/allerdata_arbo');
		// un element supprime n'a pas d'enfant en principe mais on verifie quand meme
		$enfants = allerdata_les_enfants($id_item);
		if (!count($enfants)){
			/*$dump .= "\nEnfants : ".implode(', ',$enfants);
			// les parents
			$parents = allerdata_les_parents($id_item);
			$dump .= "\nParents : ".implode(', ',$parents);
			// les revisions
			$revisions = sql_allfetsel('*','tbl_items_versions','id_item='.$id_item);
			$dump .= "\nRevisions :" .var_export($revisions,true);
	
			$file_dump = sous_repertoire(_DIR_TMP,"allerdata_delete").date("Ymd")."-".substr(md5(serialize($dump)),0,8) . ".txt";
			ecrire_fichier($file_dump,$dump);
			spip_log("poubelle id_item $id_item/'$nom' par auteur ".$GLOBALS['visiteur_session']['id_auteur'].": $file_dump",'allerdata_delete');
	
			// supprimons tout ca dans le bon ordre :
			if (count($enfants)){
				spip_log("--> deaffiliation enfants de $id_item : ".implode(',',$enfants),'allerdata_delete');
				allerdata_remove_filiation($enfants,$id_item);
			}
			if (count($parents)){
				spip_log("--> deaffiliation parents de $id_item : ".implode(',',$parents),'allerdata_delete');
				foreach ($parents as $id_parent)
					allerdata_remove_filiation($id_item,$id_parent);
			}
			sql_delete('tbl_items_versions','id_item='.intval($id_item));
			sql_delete('tbl_items','id_item='.intval($id_item));*/
			// on ne supprime rien : on met juste a la poubelle
			// comme a on garde la trace des versions et on peut revenir en arriere !
			include_spip('action/editer_tbl_item');
			instituer_tbl_item($id_item,array(/*'id_parent'=>array(),*/'statut'=>'poubelle'));
		}
	}
}

?>