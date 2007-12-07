<?php

function ccd($txt) {
	$tableau_produits = array();
	
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[] = $_REQUEST['p5'];
	
	if (!sizeof($tableau_produits)) return '[]';
	
	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	$query = "SELECT DISTINCT 
			tbl_items_1.id_item, 
			tbl_items_1.nom as nom_fm, 
			tbl_est_dans.est_dans_id_item, 
			tbl_items.id_item as id_mol, 
			tbl_items.nom AS nom_mol, 
			tbl_items.glyco
		FROM ((tbl_items 
			INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
			INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_items.id_item = tbl_est_dans_1.id_item) 
			INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans_1.est_dans_id_item = tbl_items_1.id_item
		WHERE (
			((tbl_est_dans.est_dans_id_item) In ($produits)) 
			AND ((tbl_items_1.id_type_item)=6))
		ORDER BY tbl_items_1.nom, tbl_est_dans.est_dans_id_item DESC;";
			
	/* --
	Algo à mettre en oeuvre : Si pour une des lignes renvoyées, glyco vaut "-1" (c'est bizarement un champ texte), alors :
	 - mémoriser id_item concerné (la famille moléculaire), est_dans_id_item (le produit dans le pentacle)
	 - +1 au nombre de CCD trouvés
	 Afficher qu'il y a des CCD, leur nombre, au clic, surligner les familles moléculaires concernées, et les produits concernés.
	 Au (i), appeler un popup qui va afficher la liste des molécules précises (passer en paramètre au squelette les 5 produits choisis)
	-- */
	
	$liste_prod_ccd = $liste_fm_ccd = array();
	$nb_ccd = 0;
	
	$res = spip_query($query);
		
	while ($row = spip_fetch_array($res)){
		$pos += 1;
	
		// CCD
		if ($row['glyco'] == '-1') {
			$nb_ccd ++;
			$liste_fm_ccd[$row['id_item']] = $row['nom_fm'];
			$liste_mol_ccd[$row['est_dans_id_item']] = $row['nom_mol'];
		}
	}
	
	foreach ($liste_fm_ccd as $index => $nom) {
		$final[] = array(
			'item' => $nom, 
			'link' => $index,
			'type' => _T("js_fm_grid_title")
		);
	}
	foreach ($liste_mol_ccd as $index => $nom) {
		$final[] = array(
			'item' => $nom, 
			'link' => $index,
			'type' => _T("js_mol_title")
		);
	}

	return json_encode($final);
}
?>