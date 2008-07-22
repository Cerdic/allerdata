<?php

function FamMol($item) {
	$query = "SELECT tbl_est_dans.id_item, tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.id_type_item
FROM tbl_est_dans INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
WHERE (((tbl_est_dans.id_item)=$item) AND ((tbl_items_1.id_type_item)=6));";
	$res = spip_query($query);
		$result = '(<span style="color:red;">Pas de Fam Mol</span>)';
	while ($row = spip_fetch_array($res)){
		$result = $row['id_item'].' ('.$row['nom'].')';
	}

	return $result;
}

?>