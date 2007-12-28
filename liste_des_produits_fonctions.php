<?php

function produits_suggeres($query) {
		
	include_spip('inc/charsets');
	
	//$stopwords = array("(",")",",",'/');
	//$query = str_replace($stopwords,'',trim(ereg_replace("[[:space:]]+",' ',$query)));
	$query = strtolower($query);
	$chaine = translitteration($query);
	
	$nb_elements_retournes = 20;
	$nb_elements_trouves = 0;
	
	session_start();
	if (is_array($_SESSION['produits_choisis']))
		$produits_deja_choisis = implode(",", $_SESSION['produits_choisis']);
	session_write_close();
	
	echo $produits_deja_choisis."\n";
	$sql = "SELECT tbl_items.id_item, nom, source, famille, pos, CONCAT(IF(nom IS NULL, '', CONCAT(nom,'zzz')),source) AS chaine 
			FROM tbl_items, tbl_index_items
			WHERE id_type_item IN (5,3) 
			AND tbl_items.id_item = tbl_index_items.id_item";
	if ($produits_deja_choisis)	$sql .=" AND tbl_item.id_item NOT IN(".$produits_deja_choisis.")";
	$sql .= "	AND keyword like '".addslashes($query)."%'";
	$q = spip_query($sql);
	
	$nb_elements_trouves = spip_num_rows($q);
	
	$res = $ids = array();

	$liste_noire = array();
	if (is_array($_SESSION['produits_choisis'])) $liste_noire = $_SESSION['produits_choisis'];

	while ($row = spip_fetch_array($q)) {
		if (!$row['nom']) $row['nom'] = $row['source'];
		if (!in_array($row['id_item'],$liste_noire)) {
			$res[] = $row; 
			$liste_noire[] = $row['id_item'];
		}
	}
	
	// On complète par une recherche sans accent
	$sql = "SELECT tbl_items.id_item, nom, source, famille, pos, CONCAT(IF(nom IS NULL, '', CONCAT(nom,'zzz')),source) AS chaine 
			FROM tbl_items, tbl_index_items 
			WHERE id_type_item IN (5,3) 
			AND tbl_items.id_item = tbl_index_items.id_item";
	if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
	$sql .= "	AND keyword like '".addslashes($chaine)."%'";
	$q = spip_query($sql);

	$nb_elements_trouves += spip_num_rows($q);

	while ($row = spip_fetch_array($q)) {
		if (!$row['nom']) $row['nom'] = $row['source'];
		if (!in_array($row['id_item'],$liste_noire)) {
			$res[] = $row; 
			$liste_noire[] = $row['id_item'];
		}
	}

	// On complète par une recherche plus large (20 maxi, question perfs client)
	if ($nb_elements_trouves<20) {
		$sql = "SELECT tbl_items.id_item, nom, source, famille, pos, CONCAT(IF(nom IS NULL, '', CONCAT(nom,'zzz')),source) AS chaine 
				FROM tbl_items, tbl_index_items 
				WHERE id_type_item IN (5,3) 
				AND tbl_items.id_item = tbl_index_items.id_item";
		if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
		$sql .= "	AND keyword like '%".addslashes($query)."%'
			LIMIT 0,".(20-$nb_elements_trouves);
		$q = spip_query($sql);

		$nb_elements_trouves += spip_num_rows($q);

		while ($row = spip_fetch_array($q)) {
			if (!$row['nom']) $row['nom'] = $row['source'];
			if (!in_array($row['id_item'],$liste_noire)) {
				$res[] = $row; 
				$liste_noire[] = $row['id_item'];
			}
		}
	}

	// On complète par une recherche plus large (20 maxi, question perfs client)
	if ($nb_elements_trouves<20) {
		$sql = "SELECT tbl_items.id_item, nom, source, famille, pos, CONCAT(IF(nom IS NULL, '', CONCAT(nom,'zzz')),source) AS chaine 
				FROM tbl_items, tbl_index_items 
				WHERE id_type_item IN (5,3) 
				AND tbl_items.id_item = tbl_index_items.id_item";
		if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
		$sql .= "	AND keyword like '%".addslashes($chaine)."%'
			LIMIT 0,".(20-$nb_elements_trouves);
		$q = spip_query($sql);

		$nb_elements_trouves += spip_num_rows($q);

		while ($row = spip_fetch_array($q)) {
			if (!$row['nom']) $row['nom'] = $row['source'];
			if (!in_array($row['id_item'],$liste_noire)) {
				$res[] = $row; 
				$liste_noire[] = $row['id_item'];
			}
		}
	}

	$final = $pos = $nom = $items = array();
	
	//Mise en avant
	foreach ($res as $key => $row) {
		$row['nom_mis_en_forme'] = $row['nom'];
		if ($query!=$chaine) $row['nom_mis_en_forme'] = eregi_replace($query,'<b>'.$query.'</b>',$row['nom_mis_en_forme']);
		$row['nom_mis_en_forme'] = eregi_replace($chaine,'<b>'.$chaine.'</b>',$row['nom_mis_en_forme']);
		if ($query!=$chaine) $row['source'] = eregi_replace($query,'<b>'.$query.'</b>',$row['source']);
		$row['source'] = eregi_replace($chaine,'<b>'.$chaine.'</b>',$row['source']);		
		$final[$key] = $row;
	}

	/* un tri sur la position+alpha */
	foreach ($final as $key => $row) {
	    $pos[$key]  = $row['pos'];
	    $nom[$key] = $row['nom'];
	}
  $items = array_map('strtolower', $nom);

	array_multisort($pos, SORT_ASC, $items, SORT_ASC, $final);

	if (!$nb_elements_trouves) return(json_encode(array(array('id_item' => '', 'nom' => '', 'nom_mis_en_forme' => '', 'source' => _T('ad:nothing_found')))));
	else return(json_encode($final));

}
	
?>
