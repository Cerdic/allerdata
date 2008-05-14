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

function allergenes($id_fm, $types = '7,8,10') {
	$result = '';
  $liste_id_produit = _request('liste_produits');
  $queryallergenes = "SELECT tbl_items_2.nom_court AS nom_produit, tbl_items_2.source AS source_produit, tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_items_2.id_type_item
						FROM (tbl_items 
							INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
								INNER JOIN ((tbl_items AS tbl_items_1 
									INNER JOIN tbl_est_dans AS tbl_est_dans_2 ON tbl_items_1.id_item = tbl_est_dans_2.id_item) 
										INNER JOIN tbl_items AS tbl_items_2 ON tbl_est_dans_2.est_dans_id_item = tbl_items_2.id_item) ON tbl_est_dans.id_item = tbl_items_1.id_item
						WHERE (
								((tbl_items.id_item)=$id_fm) 
								AND 
								((tbl_items_1.id_type_item) In ($types)) 
								AND 
								((tbl_items_2.id_type_item) In (5))
							)
						ORDER BY tbl_items_1.nom, tbl_items_2.nom_court, tbl_items_2.source;
				";
  $resallergenes = spip_query($queryallergenes);
	$count = 0;
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$count += 1;
		$allergenes .= '						<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'>
						<td>'.$rowallergenes['nom'].'</td>
						<td>'.$rowallergenes['nom_produit'].'</td>
						<td>'.$rowallergenes['source_produit'].'</td>
						<td style="text-align:right;">'.$rowallergenes['masse'].'</td>
						<td style="text-align:center;">'.(($rowallergenes['iuis']==1)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
						<td style="text-align:center;">'.(($rowallergenes['glyco']==-1)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
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

function allergenes_testables($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryallergenes = "SELECT tbl_items_2.nom_court AS nom_produit, tbl_items_2.source AS source_produit, tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_items_2.id_type_item
						FROM (tbl_items 
							INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
								INNER JOIN ((tbl_items AS tbl_items_1 
									INNER JOIN tbl_est_dans AS tbl_est_dans_2 ON tbl_items_1.id_item = tbl_est_dans_2.id_item) 
										INNER JOIN tbl_items AS tbl_items_2 ON tbl_est_dans_2.est_dans_id_item = tbl_items_2.id_item) ON tbl_est_dans.id_item = tbl_items_1.id_item 
					WHERE (
						((tbl_est_dans.est_dans_id_item)=$produits) 
						AND ((tbl_items_1.testable) <> 0)
						AND ((tbl_items_1.id_type_item) IN (7,8,9,10))
						AND ( NOT ISNULL(tbl_items_1.nom))
						/*AND ((tbl_items_2.id_type_item) In (5))*/
						)
					ORDER BY tbl_items_1.nom, tbl_items_2.source;";
	$resallergenes = spip_query($queryallergenes);
	$count = 0;
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$count += 1;
		$allergenes .= '						<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'>
						<td>'.$rowallergenes['nom'].'</td>
						<td>'.$rowallergenes['nom_produit'].'</td>
						<td>'.$rowallergenes['source_produit'].'</td>
						<td style="text-align:center;font-size:1.5em;">'.(($rowallergenes['glyco']<>0)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
						</tr>';
	}
	$result .= $allergenes;

	return $result;
}

