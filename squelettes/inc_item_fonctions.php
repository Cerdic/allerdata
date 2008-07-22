<?php
function listetype5($produits) {
	if (!is_numeric($produits)) return;
	
	$query = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.testable
				FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
					INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
				WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items.id_type_item)=3) 
						AND ((tbl_items_1.id_type_item)=5)
						AND ( NOT ISNULL(tbl_items_1.nom))
					)
				ORDER BY tbl_items_1.nom;";
	$res = spip_query($query);
	$result = '';
	$count = 0;
	while ($row = spip_fetch_array($res)){
		$count += 1;
		$result .= '<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td><a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['nom']).'\',\'?page=popup_item&amp;id_item='.$row['id_item'].'\'); return false">'.$row['nom'].'</a></td>';
		$querysource = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=4)
							AND ( NOT ISNULL(tbl_items_1.nom)
							AND (directement_contenu<>0))
							)
						ORDER BY tbl_items_1.nom;";
		$ressource = spip_query($querysource);
		$source ='';
		while ($rowsource = spip_fetch_array($ressource)){
			if ($source) {
				$source .= ', '.$rowsource['nom'];
				$testable |= $rowsource['nom'];
			} else {
				$source .= $rowsource['nom'];
				$testable |= $rowsource['nom'];
			}
		}
		$source = '<td><i>'.$source.'</i></td>';
		$result .= $source;
		$queryfamille = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
						FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
							INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
						WHERE (
							((tbl_items.id_item)=".$row['id_item'].") 
							AND ((tbl_items_1.id_type_item)=2)
							AND ( NOT ISNULL(tbl_items_1.nom))
							)
						ORDER BY tbl_items_1.nom;";
		$resfamille = spip_query($queryfamille);
		$famille ='';
		while ($rowfamille = spip_fetch_array($resfamille)){
			if ($famille) { 
				$famille .= ', '.$rowfamille['nom'];
			} else {
				$famille .= $rowfamille['nom'];
			}
		}
		$result .= '<td>'.$famille.'</td>';
		$result .= '<td style="text-align:center;font-size:1.5em;">'.(($rowsource['testable']==-1)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>';
		$result .= '</tr>';
	}

	return $result;
}

function source($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$t_exclude = $t_id_sources = $t_nom_sources = array();
	
  $querysource = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.est_dans_id_item = tbl_items_1.id_item
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item)=4)
						AND ( NOT ISNULL(tbl_items_1.nom)
						AND (directement_contenu<>0))
						)
					ORDER BY tbl_items_1.nom;";
	$ressource = spip_query($querysource);
  
  /* filtre pour ne pas garder le source générique */
  while ($rowsource = spip_fetch_array($ressource)){
		$t_id_sources[$rowsource['id_item']] = $rowsource['id_item'];
		$t_nom_sources[$rowsource['id_item']] = $rowsource['nom'];
	}
  foreach($t_id_sources as $id) {
    $filtre = spip_query("SELECT id_item
          FROM tbl_est_dans
          WHERE id_item = $id
          AND est_dans_id_item IN (".implode(",",$t_id_sources).")
          AND est_dans_id_item != id_item
    ");
    if (spip_num_rows($filtre)) {
      while($row = spip_fetch_array($filtre)) {
        unset($t_nom_sources[$row['id_item']]);
      }
    }
  }
  
	return implode(", ", $t_nom_sources);
}

function allergenes($produits, $types = '7,8,10') {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryallergenes = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_items_1.testable, tbl_items_1.fonction_classification
					 FROM (tbl_est_dans 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) 
					WHERE (
						((tbl_est_dans.est_dans_id_item)=$produits) 
						AND ((tbl_items_1.id_type_item) IN ($types))
						AND ( NOT ISNULL(tbl_items_1.nom))
						)
					ORDER BY tbl_items_1.nom;";
	$resallergenes = spip_query($queryallergenes);
	$count = 0;
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$count += 1;
		$allergenes .= '						<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'>
						<td>'.(($rowallergenes['fonction_classification'] == $rowallergenes['nom']) ? '':$rowallergenes['nom']).'</td>
						<td>'.$rowallergenes['fonction_classification'].'</td>
						<td style="text-align:right;">'.$rowallergenes['masse'].'</td>
						<td style="text-align:center;font-size:1.5em;">'.(($rowallergenes['iuis']<>0)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
						<td style="text-align:center;font-size:1.5em;">'.(($rowallergenes['glyco']<>0)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
						</tr>';
	}
	$result .= $allergenes;

	return $result;
}

function allergenes_testables($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryallergenes = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_items_1.masse, tbl_items_1.iuis, tbl_items_1.glyco, tbl_items_1.testable, tbl_items_1.fonction_classification
					 FROM (tbl_est_dans 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) 
					WHERE (
						((tbl_est_dans.est_dans_id_item)=$produits) 
						AND ((tbl_items_1.testable) <> 0)
						AND ((tbl_items_1.id_type_item) IN (7,8,9,10))
						AND ( NOT ISNULL(tbl_items_1.nom_complet))
						)
					ORDER BY tbl_items_1.nom_complet;";
	$resallergenes = spip_query($queryallergenes);
	$count = 0;
	while ($rowallergenes = spip_fetch_array($resallergenes)){
		$count += 1;
		$allergenes .= '						<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'>
						<td>'.(($rowallergenes['fonction_classification'] == $rowallergenes['nom']) ? '':$rowallergenes['nom']).'</td>
						<td>'.$rowallergenes['fonction_classification'].'</td>
						<td style="text-align:center;font-size:1.5em;">'.(($rowallergenes['glyco']<>0)?'<img src="squelettes/img/icon_accept.gif" />':'').'</td>
						</tr>';
	}
	$result .= $allergenes;

	return $result;
}
