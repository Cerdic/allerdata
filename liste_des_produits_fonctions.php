<?php

function produits_suggeres($query) {
		
	include_spip('inc/charsets');
	
	// $query = strtolower($query); // plante sur le serveur de dev de jacques ==> inversion de l'ordre des 2 lignes.
	$chaine = translitteration($query);
	$chaine = strtolower($chaine);
	
	$nb_elements_retournes = 20;
	$nb_elements_trouves = 0;
	$tableau_produits = $liste_noire = $res = $ids = array();
  $produits_deja_choisis = '';
	
  // Exclure les produits déjà dans les zones de saisie du penta
  // Ainsi que leurs parents dans l'ordre de composition
  // Cette liste est maintenue lors du calcul des suggestions (et purgée au chargement de la page)
	session_start();
	if (isset($_SESSION['produits_choisis']) && is_array($_SESSION['produits_choisis'])) {
    $liste_noire = $_SESSION['produits_choisis'];
		$produits_deja_choisis = implode(",", $liste_noire);
  }
  
  session_write_close();
  
	$sql = "SELECT tbl_items.id_item, nom, nom_court, source, famille
			FROM tbl_items 
			WHERE id_type_item IN (5,3) ";
	if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
	$sql .= "	AND chaine_alpha like '".addslashes($chaine)."%'";
	$sql .= " ORDER BY id_type_item , nom";
	$q = spip_query($sql);

	$nb_elements_trouves += spip_num_rows($q);

	while ($row = spip_fetch_array($q)) {
		if (!$row['nom']) $row['nom'] = $row['source'];
		if (!in_array($row['id_item'],$liste_noire)) {
			$res[] = $row; 
			$liste_noire[] = $row['id_item'];
		}
	}

	$sql = "SELECT tbl_items.id_item, nom, nom_court, source, famille
			FROM tbl_items 
			WHERE id_type_item IN (5,3) ";
	if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
	$sql .= "	AND chaine_alpha like '% ".addslashes($chaine)."%'";
	$sql .= " ORDER BY id_type_item DESC, nom";
	$q = spip_query($sql);

	$nb_elements_trouves += spip_num_rows($q);

	while ($row = spip_fetch_array($q)) {
		if (!$row['nom']) $row['nom'] = $row['source'];
		if (!in_array($row['id_item'],$liste_noire)) {
			$res[] = $row; 
			$liste_noire[] = $row['id_item'];
		}
	}

	// On complete par une recherche plus large (20 maxi, question perfs client)
	if ($nb_elements_trouves<20) {
		$sql = "SELECT tbl_items.id_item, nom, nom_court, source, famille
				FROM tbl_items 
				WHERE id_type_item IN (5,3)"; 
		if ($liste_noire)	$sql .=" AND tbl_items.id_item NOT IN(".implode(',',$liste_noire).")";
		$sql .= "	AND chaine_alpha like '%".addslashes($chaine)."%'
			ORDER BY nom
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

	if (_request('debug')) {var_dump($final);die();}
	
	if (!$nb_elements_trouves) return(json_encode(array(array('id_item' => '', 'nom' => '', 'nom_mis_en_forme' => '', 'source' => _T('ad:nothing_found').' pour '.$chaine))));
	else return(json_encode($final));

}
	
?>
