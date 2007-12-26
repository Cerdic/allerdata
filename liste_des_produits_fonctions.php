<?php

function produits_suggeres($query) {
		
	include_spip('inc/charsets');
	
	$chaine = translitteration($query);
	
	spip_log("recherche pour ".$query.' : '.$chaine);
	$nb_elements_retournes = 10;
	$nb_elements_trouves = 0;
	
	$chaine = strtolower($chaine);
	
	session_start();
	if (is_array($_SESSION['produits_choisis']))
		$produits_deja_choisis = implode(",", $_SESSION['produits_choisis']);
	session_write_close();
	
	$sql = "SELECT id_item, nom, source, famille FROM tbl_items WHERE id_type_item IN (5,3) ";
	if ($produits_deja_choisis)	$sql .="AND id_item NOT IN(".$produits_deja_choisis.")";
	$sql .= "	AND ( nom like '".addslashes($query)."%'
		OR source like '".addslashes($query)."%')
		ORDER BY nom";
	$q = spip_query($sql);
	
	$nb_elements_trouves = spip_num_rows($q);
	
	$res = $ids = array();

	$liste_noire = array();
	if (is_array($_SESSION['produits_choisis'])) $liste_noire = $_SESSION['produits_choisis'];

	while ($row = spip_fetch_array($q)) {
		if (!$row['nom']) $row['nom'] = $row['source'];
		$res[] = $row; 
		$liste_noire[] = $row['id_item'];
	}
	
	// On complète par une recherche plus large (20 maxi, question perfs client)
	if ($nb_elements_trouves<20) {
		$sql = "SELECT id_item, nom, source, famille FROM tbl_items 
					WHERE id_type_item IN (5,3) ";
		if ($liste_noire)	$sql .="AND id_item NOT IN(".implode(',',$liste_noire).")";
		$sql .= "	AND ( nom_sans_accent like '%".addslashes($chaine)."%'
			OR source_sans_accent like '%".addslashes($chaine)."%')
			ORDER BY nom
			LIMIT 0,".(20-$nb_elements_trouves);
		$q = spip_query($sql);

		$nb_elements_trouves += spip_num_rows($q);

		while ($row = spip_fetch_array($q)) {
			if (!$row['nom']) $row['nom'] = $row['source'];
			$res[] = $row; 
		}
	}

	if (!$nb_elements_trouves) return(json_encode(array(array('id_item' => '', 'nom' => '', 'source' => _T('ad:nothing_found')))));
	else return(json_encode($res));

}
	
?>