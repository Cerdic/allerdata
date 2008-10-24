<?php
function familles_moleculaires($txt) {
	$tableau_produits = array();
	
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[1] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[2] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[3] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[4] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[5] = $_REQUEST['p5'];
	
	if (!sizeof($tableau_produits)) return '[]';
	
  // tri pour signature unique
  sort($tableau_produits);
  $signature = implode(',', $tableau_produits);
  $q = spip_query("select resultat_json from cache_requetes where tuple='".mysql_real_escape_string($signature)."' and page='liste_des_familles'");
  if (spip_num_rows($q)) {
    $r = spip_fetch_array($q);
    return $r['resultat_json'];
  }

	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	/* Note : le test clinique est stocké dans le champ "descriptif" de la FM */
	$query = "SELECT DISTINCT 
			tbl_items_1.id_item, 
			tbl_items_1.nom, 
			tbl_est_dans.est_dans_id_item, 
			tbl_items.nom as nom2, 
			tbl_est_dans_1.est_dans_id_item as id_dans_item2,
			tbl_items_1.representatif
		FROM (((tbl_items 
			INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
			INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_items.id_item = tbl_est_dans_1.id_item) 
			INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans_1.est_dans_id_item = tbl_items_1.id_item) 
		WHERE (((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_items_1.id_type_item)=6))
		ORDER BY tbl_items_1.nom, tbl_est_dans.est_dans_id_item DESC;"; 
			
	$res = spip_query($query);
	$result = '[';
	$id_item_precedent = 0;
	$compte_produit = 1;
	$liste_produit = $temp = $tri = $final = array();
	$pos = 0;
		
	while ($row = spip_fetch_array($res)){
		$pos += 1;
	
		$prod = $row['est_dans_id_item'];
		$nom = $row['nom'];
		$id_item = $row['id_item'];
				
		/* Pour retrouver le tri par nom, il faut mémoriser la position du premier item enregistré */
		if (!isset($temp[$id_item])) $temp[$id_item] = array('card' =>0, 'pos' =>$pos, 'est_dans' => array(), 'nom' => $nom, 'id_item' => $id_item);
		
		/* on garde aussi les produits concernés par la famille moléculaire */
		if (!in_array($prod, $temp[$id_item]['est_dans'])) {
			$temp[$id_item]['est_dans'][] = $prod;		
			$temp[$id_item]['card'] +=1;
			$temp[$id_item]['representatif'] = $row['representatif'];
		}
	}
	
	foreach ($temp as $id_item => $ligne) {
		if (!isset($tri[$ligne['card']])) 
			$tri[$ligne['card']] = array();
			
		$tri[$ligne['card']][$ligne['pos']] = $ligne;
	}

	// Les clé nous permettent de retrouver le bon ordre
	krsort($tri);
	
	foreach ($tri as $ord => $pos) {
		ksort($pos);
		foreach ($pos as $index => $ligne) {

			$final[] = array(
				'nom' => $ligne['nom'].'==>['.implode(',',$ligne['est_dans']).']', 
				'nb_item' => $ligne['card'], 
				'test' => $ligne['representatif'],
				'id_item' => '"'.$ligne['id_item'].'&liste_produits='.implode(',',$ligne['est_dans']).'"',
				'est_dans' => implode(',',$ligne['est_dans']) 
			);
		}
	}

	if (_request('debug')) {var_dump($final);die();}
	
  $output = json_encode($final);
  
  // On stocke pour un prochain appel
  spip_query("INSERT INTO cache_requetes (page,tuple,resultat_json,date_maj) 
              VALUES('liste_des_familles',
                     '".mysql_real_escape_string($signature)."',
                     '".mysql_real_escape_string($output)."',
                     NOW())");

	return $output;

	

}
?>