<?php
function listetype5($produits) {
	if (!is_numeric($produits)) return;
	
	$query = "SELECT tbl_items_1.id_item, tbl_items_1.nom
				FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
					INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
				WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items.id_type_item)=3) 
						AND ((tbl_items_1.id_type_item)=5)
					);";
	$res = spip_query($query);
	$result = '';
	while ($row = spip_fetch_array($res)){
		$result .= '<li>'.$row['nom'];
		$querysource = "SELECT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=4)
							);";
		$ressource = spip_query($querysource);
		while ($rowsource = spip_fetch_array($ressource)){
			$source = '(Source&nbsp;: '.$rowsource['nom'].')';
		}
		$queryfamille = "SELECT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=2)
							);";
		$resfamille = spip_query($queryfamille);
		while ($rowfamille = spip_fetch_array($resfamille)){
			$famille = '(Famille taxonomique&nbsp;: '.$rowfamille['nom'].')';
		}
		$result .= $source.'</li>';
	}

	return $result;
}
