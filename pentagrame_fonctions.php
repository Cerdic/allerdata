<?php
function familles_moleculaires($p1,$p2,$p3,$p4,$p5) {
	$tableau_produits = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	if (is_numeric($p3)) $tableau_produits[] = $p3;
	if (is_numeric($p4)) $tableau_produits[] = $p4;
	if (is_numeric($p5)) $tableau_produits[] = $p5;
	
	$produits = implode(",", $tableau_produits);
	
	$query = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_est_dans.est_dans_id_item
		FROM (((tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
			INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_items.id_item = tbl_est_dans_1.id_item) 
			INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans_1.est_dans_id_item = tbl_items_1.id_item) 
			INNER JOIN tbl_types_items ON tbl_items_1.id_type_item = tbl_types_items.id_type_item
		WHERE (((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_types_items.id_type_item)=6))
		ORDER BY tbl_items_1.nom, tbl_est_dans.est_dans_id_item DESC;";
		
	$res = spip_query($query);
	$result = '';
	$id_item_precedent = 0;
	$compte_produit = 1;
	while ($row = spip_fetch_array($res)){
		if ($id_item_precedent == $row['id_item']) {
			$liste_produit .= ','.$row['est_dans_id_item'];
			$compte_produit += 1;
		} else {
			if ($id_item_precedent != 0) {
				$result .= ", $compte_produit ($liste_produit)<br />";
			}
			$result .= $row['id_item'].', '.$row['nom'];
			$liste_produit = $row['est_dans_id_item'];
			$compte_produit = 1;
		}
		$id_item_precedent = $row['id_item'];
	}
	if ($id_item_precedent != 0) {
		$result .= ", $compte_produit ($liste_produit)<br />";
	}

	return $result;
}

function rc($p1,$p2,$p3,$p4,$p5) {
	$tableau_produits = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	if (is_numeric($p3)) $tableau_produits[] = $p3;
	if (is_numeric($p4)) $tableau_produits[] = $p4;
	if (is_numeric($p5)) $tableau_produits[] = $p5;
	
	$produits = implode(",", $tableau_produits);
	
	$query = "SELECT DISTINCT 
			tbl_reactions_croisees.id_reaction_croisee, 
			tbl_items.id_item AS idp1, 
			tbl_items.nom AS p1, 
			tbl_reactions_croisees.id_produit1, 
			tbl_reactions_croisees.fleche_sens1, 
			tbl_reactions_croisees.fleche_sens2, 
			tbl_reactions_croisees.id_produit2, 
			tbl_items_1.id_item AS idp2, 
			tbl_items_1.nom AS p2, 
			tbl_reactions_croisees.produits_differents, 
			tbl_est_dans.est_dans_id_item AS id_s1, 
			tbl_est_dans_1.est_dans_id_item AS id_s2
		FROM ((((tbl_est_dans AS tbl_est_dans_1 INNER JOIN (tbl_reactions_croisees INNER JOIN tbl_est_dans ON tbl_reactions_croisees.id_produit1 = tbl_est_dans.id_item) ON tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2) INNER JOIN tbl_items ON tbl_est_dans_1.id_item = tbl_items.id_item) INNER JOIN tbl_types_items ON tbl_items.id_type_item = tbl_types_items.id_type_item) INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) INNER JOIN tbl_types_items AS tbl_types_items_1 ON tbl_items_1.id_type_item = tbl_types_items_1.id_type_item
		WHERE (((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_est_dans_1.est_dans_id_item) In ($produits)));";
	$res = spip_query($query);
		$result = '';
	while ($row = spip_fetch_array($res)){
		$result .= $row['id_reaction_croisee'].' ('.$row['p1'].', '.' ('.$row['idp1'].'), '.$row['fleche_sens1'].$row['fleche_sens2'].$row['p2'].' ('.$row['idp2'].')) ('.$row['id_s1'].','.$row['id_s2'].')<br />';
	}

	return $result;
}

function rc_suggestions($p1,$p2,$p3,$p4,$p5) {
	$tableau_produits = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	if (is_numeric($p3)) $tableau_produits[] = $p3;
	if (is_numeric($p4)) $tableau_produits[] = $p4;
	if (is_numeric($p5)) $tableau_produits[] = $p5;
	
	$produits = implode(",", $tableau_produits);
	
	$query = "SELECT DISTINCT 
			tbl_reactions_croisees.id_reaction_croisee, 
			tbl_types_items.pentacle,
			tbl_items.nom as p1, 
			tbl_reactions_croisees.id_produit1, 
			tbl_reactions_croisees.fleche_sens1, 
			tbl_reactions_croisees.fleche_sens2, 
			tbl_reactions_croisees.id_produit2, 
			tbl_items_1.nom as p2, 
			tbl_types_items_1.pentacle, 
			tbl_reactions_croisees.produits_differents
		FROM ((((tbl_est_dans AS tbl_est_dans_1 INNER JOIN (tbl_reactions_croisees INNER JOIN tbl_est_dans ON tbl_reactions_croisees.id_produit1 = tbl_est_dans.id_item) ON tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2) INNER JOIN tbl_items ON tbl_est_dans_1.id_item = tbl_items.id_item) INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) INNER JOIN tbl_types_items ON tbl_items.id_type_item = tbl_types_items.id_type_item) INNER JOIN tbl_types_items AS tbl_types_items_1 ON tbl_items_1.id_type_item = tbl_types_items_1.id_type_item
		WHERE (((tbl_types_items.pentacle)=True) AND ((tbl_types_items_1.pentacle)=True) AND ((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_est_dans_1.est_dans_id_item) Not In ($produits)) AND (((tbl_reactions_croisees.fleche_sens1)=1 Or (tbl_reactions_croisees.fleche_sens2)=1))) OR (((tbl_types_items.pentacle)=True) AND ((tbl_types_items_1.pentacle)=True) AND ((tbl_est_dans.est_dans_id_item) Not In ($produits)) AND ((tbl_est_dans_1.est_dans_id_item) In ($produits)) AND (((tbl_reactions_croisees.fleche_sens1)=1 Or (tbl_reactions_croisees.fleche_sens2)=1)))
";
	$res = spip_query($query);
		$result = '';
	while ($row = spip_fetch_array($res)){
		$result .= $row['id_reaction_croisee'].' ('.$row['p1'].', '.$row['p2'].')<br />';
	}

	return $result;
}

?>