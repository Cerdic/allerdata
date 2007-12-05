<?php
function suggestions($txt) {
	$tableau_produits = array();
	
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[] = $_REQUEST['p5'];
	
	if (!sizeof($tableau_produits)) return '[]';
	
	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	/* Sens Aller : p1 vers p2 */

	$query = "
	SELECT tbl_items.id_item AS idp1, 
		tbl_items.nom AS p1, 
		tbl_reactions_croisees.fleche_sens1, 
		tbl_reactions_croisees.fleche_sens2, 
		tbl_items_1.id_item AS idp2, 
		tbl_items_1.nom AS p2
	FROM tbl_est_dans, tbl_est_dans AS tbl_est_dans_1, tbl_reactions_croisees, tbl_items, tbl_items AS tbl_items_1
	WHERE tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1
		AND tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2
		AND tbl_est_dans.id_item = tbl_items.id_item
		AND tbl_est_dans_1.id_item = tbl_items_1.id_item
		AND tbl_items.id_item
			IN ($produits ) 
		AND tbl_items_1.id_item NOT 
			IN ($produits) ".
	//	AND tbl_items_1.affichage_suggestion =1
	"GROUP BY tbl_reactions_croisees.id_reaction_croisee
	ORDER BY tbl_items.nom
	";
		
	$res = spip_query($query);
	$liste = $suggestions = $nom = array();
		
	spip_log($query);
	
	while ($row = spip_fetch_array($res)){
			
		$nom[$row['idp1']] = $row['p1'];
		$nom[$row['idp2']] = $row['p2'];
		
		/* 3 cas possibles : NULL, 0 ou 1  */
		if (!isset($liste[$row['idp1']])) $liste[$row['idp1']] = array();
		if (!isset($liste[$row['idp1']][$row['idp2']])) 
			$liste[$row['idp1']][$row['idp2']] = array();
		if (!isset($liste[$row['idp1']][$row['idp2']])) {
			$s1 = $s2 = null;
			if (!is_null($row['fleche_sens1'])) $s1 = $row['fleche_sens1'];
			if (!is_null($row['fleche_sens2'])) $s2 = $row['fleche_sens2'];
			$liste[$res['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		else {
			$s1 = $liste[$row['idp1']][$row['idp2']]['s1'];
			$s2 = $liste[$row['idp1']][$row['idp2']]['s2'];
			if (!is_null($row['fleche_sens1'])) $s1 += $row['fleche_sens1'];
			if (!is_null($row['fleche_sens2'])) $s2 += $row['fleche_sens2'];
			$liste[$row['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		
	}
	
	/* Sens Retour : p2 vers p1 */

	$query = "
	SELECT tbl_items_1.id_item AS idp1, 
		tbl_items_1.nom AS p1, 
		tbl_reactions_croisees.fleche_sens1, 
		tbl_reactions_croisees.fleche_sens2, 
		tbl_items.id_item AS idp2, 
		tbl_items.nom AS p2
	FROM tbl_est_dans, tbl_est_dans AS tbl_est_dans_1, tbl_reactions_croisees, tbl_items, tbl_items AS tbl_items_1
	WHERE tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1
		AND tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2
		AND tbl_est_dans.id_item = tbl_items.id_item
		AND tbl_est_dans_1.id_item = tbl_items_1.id_item
		AND tbl_items_1.id_item
			IN ($produits ) 
		AND tbl_items.id_item NOT 
			IN ($produits) ".
	//	AND tbl_items.affichage_suggestion =1
	"GROUP BY tbl_reactions_croisees.id_reaction_croisee
	ORDER BY tbl_items_1.nom
	";
		
	spip_log($query);
	
	$res = spip_query($query);
	while ($row = spip_fetch_array($res)){
			
		$nom[$row['idp1']] = $row['p1'];
		$nom[$row['idp2']] = $row['p2'];
		
		/* 3 cas possibles : NULL, 0 ou 1  */
		if (!isset($liste[$row['idp1']])) $liste[$row['idp1']] = array();
		if (!isset($liste[$row['idp1']][$row['idp2']])) 
			$liste[$row['idp1']][$row['idp2']] = array();
		if (!isset($liste[$row['idp1']][$row['idp2']])) {
			$s1 = $s2 = null;
			if (!is_null($row['fleche_sens1'])) $s1 = $row['fleche_sens1'];
			if (!is_null($row['fleche_sens2'])) $s2 = $row['fleche_sens2'];
			$liste[$res['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		else {
			$s1 = $liste[$row['idp1']][$row['idp2']]['s1'];
			$s2 = $liste[$row['idp1']][$row['idp2']]['s2'];
			if (!is_null($row['fleche_sens1'])) $s1 += $row['fleche_sens1'];
			if (!is_null($row['fleche_sens2'])) $s2 += $row['fleche_sens2'];
			$liste[$row['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		
	}
	
	// Ventilation et calcul des types de rc
	foreach ($liste as $idp1 => $l_idp1) {
		foreach ($l_idp1 as $idp2 => $l_idp2) {
			$t = sizeof($l_idp2);
			$sens1 = is_null($l_idp2['s1'])?'':
				(($l_idp2['s1']===0)?'jamais':
					(($l_idp2['s1']!=$t)?'toujours':
						'discordant'
					)
				);
			$sens2 = is_null($l_idp2['s2'])?'':
				(($l_idp2['s2']===0)?'jamais':
					(($l_idp2['s2']!=$t)?'toujours':
						'discordant'
					)
				);
			$suggestions[] = array('nom1'=>$nom[$idp1], 'nom2'=>$nom[$idp2], 'type_rc1' => $sens1, 'type_rc2' => $sens2);
		}
	}

	
	return json_encode($suggestions);
	

}
?>