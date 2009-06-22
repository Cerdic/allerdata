<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

//
// <BOUCLE(tbl_bibliographies)>
//
function boucle_tbl_bibliographies_dist($id_boucle, &$boucles) {
	$boucle = &$boucles[$id_boucle];
	$id_table = $boucle->id_table;
	$mstatut = $id_table .'.statut';

	// Restreindre aux elements publies
	if (!isset($boucle->modificateur['criteres']['statut'])) {
		if (!$GLOBALS['var_preview']) {
			array_unshift($boucle->where,array("'='", "'$mstatut'", "'\\'publie\\''"));
		} else
			array_unshift($boucle->where,array("'IN'", "'$mstatut'", "'(\\'publie\\',\\'prop\\')'"));
	}
	return calculer_boucle($id_boucle, $boucles); 
}


function biblio_trous(){
	$res = sql_fetsel("count(id_bibliographie) as n,max(id_bibliographie) as m","tbl_bibliographies");
	if ($res['m']>$res['n']){
		$max = intval($res['m']);
		$i=0;
		$trous = array();
		do{
			$j = $i;
			$i = min($i+1000,$max);
			$exists = sql_allfetsel('id_bibliographie',"tbl_bibliographies","id_bibliographie>$j AND id_bibliographie<=$i");
			$exists = array_map('reset',$exists);
			$exists = array_flip($exists);
			for($k=$j+1;$k<=$i AND $k<=$max;$k++){
				if (!isset($exists[$k]))
					$trous[] = $k;
			}

		} while($i<$max);
		$total = count($trous);
		return "<a href='#' onclick='jQuery(this).next().toggle(\"fast\");return false;'>$total biblio manquante(s)...</a>"
		. "<div style='display:none;'>" . implode(", ",$trous)."</div>";
	}
}
?>