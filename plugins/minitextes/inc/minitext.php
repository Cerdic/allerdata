<?php
/*
 * Plugin Mini-textes
 * (c) 2010 Cedric MORIN
 * Distribue sous licence GPL
 *
 */


/**
 * Retrouver les produits d'un mini texte :
 *   - soit des produits en direct si type 1 (produits) ou 3 (famille mol)
 *   - soit des couples de produit
 * @param <type> $id_minitexte
 */
function minitext_les_parents($id_minitexte){

	$parents = array();
	$ids = array_map('reset',sql_allfetsel("id_item", 'tbl_items', "id_minitexte=".intval($id_minitexte)));

	$couples = sql_allfetsel("id_item_1,id_item_2", 'tbl_minitextes_items', "id_minitexte=".intval($id_minitexte));

	return array_merge($ids,$couples);
	
}

function minitext_couple_in_liste($couple,$liste){
	if (count($couple)!=2)
		return false;
	foreach($liste as $c){
		if (count($c)==2 AND !count(array_diff($couple,$c)))
			return $c;
	}
	return false;
}

function minitext_modifier_les_parents($id_minitexte,$id_parent){
	include_spip('base/abstract_sql');
	$olds = minitext_les_parents($id_minitexte);
	$remove_id = array();
	$remove_couples = array();


	// si c'est un mini-texte lie a un ou plusieurs items en direct
	// $id_parent est une liste d'id
	if (!is_array(reset($id_parent))){
		foreach ($olds as $k=>$old) {
			if (!is_array($old)) {
				if (!in_array($old, $id_parent))
					$remove_id[] = $old;
			}
			else {
				$remove_couples[] = $old;
				unset($olds[$k]);
			}
		}

		$update = array_diff($id_parent, $olds);

		// placer le mini-texte sur les id restant
		sql_updateq("tbl_items", array("id_minitexte"=>$id_minitexte), sql_in('id_item',$update));

	}

	// sinon c'est une liste de couples
	else {
		foreach ($olds as $k=>$old) {
			if (!is_array($old)) {
				$remove_id[] = $old;
				unset($olds[$k]);
			}
			else {
				if (!$c = minitext_couple_in_liste($old, $id_parent))
					$remove_couples[] = $old; // on retirera ce couple
				else
					$id_parent = array_diff($id_parent,$c); // on enleve $c de la liste car deja en parent
			}
		}

		// on insere chaque couple dans les deux sens possibles
		foreach ($id_parent as $c){
			sql_insertq_multi('tbl_minitextes_items', array(
				array('id_minitexte'=>$id_minitexte,'id_item_1'=>reset($c),'id_item_2'=>end($c)),
				array('id_minitexte'=>$id_minitexte,'id_item_2'=>reset($c),'id_item_1'=>end($c)),
				)
			);
		}
	}

	// enlever le mini-texte sur les anciens id
	sql_updateq("tbl_items", array("id_minitexte"=>0), sql_in('id_item',$remove_id));

	// et retirer les couples
	foreach ($remove_couples as $c){
		$sum = reset($c)+end($c);
		sql_delete("tbl_minitextes_items",
			"id_minitexte=".intval($id_minitexte).
			" AND id_item_1+id_item_2=".intval($sum()).
			" AND ".sql_in('id_item_1',$c));
	}

}


?>