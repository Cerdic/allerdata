<?php
function source($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$querysource = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item)=4)
						AND ( NOT ISNULL(tbl_items_1.nom))
						)
					ORDER BY tbl_items_1.nom;";
	$ressource = spip_query($querysource);
	while ($rowsource = spip_fetch_array($ressource)){
		$source .= '<li>Source&nbsp;: '.$rowsource['nom'].'</li>';
	}
	$result .= $source;

	return $result;
}

function allergenes($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryallergenes = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_items_1.fonction_classification, tbl_niveaux_allergenicite.niveau_de_preuve
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
						INNER JOIN tbl_niveaux_allergenicite ON tbl_items_1.id_niveau_allergenicite = tbl_niveaux_allergenicite.id_niveau_allergenicite
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item) IN (7,8))
						AND ( NOT ISNULL(tbl_items_1.nom))
						)
					ORDER BY tbl_items_1.nom;";
	$resallergenes = spip_query($queryallergenes);
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$allergenes .= '						<tr>
						<td>'.$rowallergenes['nom'].'</td>
						<td>'.$rowallergenes['fonction_classification'].'</td>
						<td>'.$rowallergenes['masse'].'</td>
						<td>'.$rowallergenes['iuis'].'</td>
						<td>'.$rowallergenes['glyco'].'</td>
						<td>'.$rowallergenes['allergenicite'].'</td>
						</tr>';
	}
	$result .= $allergenes;

	return $result;
}

function produit($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryproduit = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item)=5)
						AND ( NOT ISNULL(tbl_items_1.nom))
						)
					ORDER BY tbl_items_1.nom;";
	$resproduit = spip_query($queryproduit);
	while ($rowproduit = spip_fetch_array($resproduit)){
		$produit .= '<li>Produit&nbsp;: '.$rowproduit['nom'].'</li>';
	}
	$result .= $produit;

	return $result;
}
