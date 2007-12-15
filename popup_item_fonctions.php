<?php
function listetype5($produits) {
	if (!is_numeric($produits)) return;
	
	$query = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
				FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
					INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
				WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items.id_type_item)=3) 
						AND ((tbl_items_1.id_type_item)=5)
					)
				ORDER BY tbl_items_1.nom;";
	$res = spip_query($query);
	$result = '';
	while ($row = spip_fetch_array($res)){
		$result .= '<li>'.$row['nom'];
		$querysource = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=4)
							)
						ORDER BY tbl_items_1.nom;";
		$ressource = spip_query($querysource);
		while ($rowsource = spip_fetch_array($ressource)){
			$source .= '(Source&nbsp;: '.$rowsource['nom'].')';
		}
		$queryfamille = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=2)
							)
						ORDER BY tbl_items_1.nom;";
		$resfamille = spip_query($queryfamille);
		while ($rowfamille = spip_fetch_array($resfamille)){
			$famille .= '(Famille taxonomique&nbsp;: '.$rowfamille['nom'].')';
		}
		$result .= $source.'</li>';
	}

	return $result;
}

function source($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$querysource = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item)=4)
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
	$queryallergenes = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_niveaux_allergenicite.niveau_de_preuve
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
						INNER JOIN tbl_niveaux_allergenicite ON tbl_items_1.id_niveau_allergenicite = tbl_niveaux_allergenicite.id_niveau_allergenicite
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item) IN (7,8))
						)
					ORDER BY tbl_items_1.nom;";
	$resallergenes = spip_query($queryallergenes);
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$allergenes .= '						<tr>
						<td>'.$rowallergenes['nom'].'</td>
						<td>???</td>
						<td>'.$rowallergenes['masse'].'</td>
						<td>'.$rowallergenes['iuis'].'</td>
						<td>'.$rowallergenes['glyco'].'</td>
						<td>'.$rowallergenes['allergenicite'].'</td>
						</tr>';
	}
	$result .= $allergenes;

	return $result;
}
