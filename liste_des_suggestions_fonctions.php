<?php

function cmp($a, $b) 
/* fonction de tri inverse via uksort */
{
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function suggestions($txt) {
	$tableau_produits = $t_suggestions = array();
	$prem = array(0,97); /* 2 nombres premiers avant 100 avec un delta de 1/8 */
	/* Correction : ne plus tenir compte de l'absence testée de réactivité */
	
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[] = $_REQUEST['p5'];
	
	session_start();
	$_SESSION['produits_choisis'] = $tableau_produits;
	if ($tableau_produits) {
		$res = spip_query("select id_item from tbl_est_dans where est_dans_id_item IN (".implode(',',$tableau_produits).")");
		while ($row = spip_fetch_array($res)){
			$_SESSION['produits_choisis'][] = $row['id_item'];
		}
	}
	session_write_close();

	if (!sizeof($tableau_produits)) return '[]';
	
	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	/* Mix des sens des RC */
	$query = "
	SELECT tbl_items.id_item AS idp1, 
		tbl_items_1.id_item AS idp2
	FROM tbl_est_dans, tbl_est_dans AS tbl_est_dans_1, tbl_reactions_croisees, tbl_items, tbl_items AS tbl_items_1
	WHERE tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1
		AND tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2
		AND tbl_est_dans.id_item = tbl_items.id_item
		AND tbl_est_dans_1.id_item = tbl_items_1.id_item
		AND (tbl_reactions_croisees.fleche_sens1 = 1 OR tbl_reactions_croisees.fleche_sens1 = 1)
		AND 
			(
				( tbl_items.id_item IN ($produits ) 
					AND tbl_items_1.id_item NOT IN ($produits)
					AND tbl_items_1.id_type_item in (5,3)
				)
			OR
				(
					tbl_items_1.id_item IN ($produits ) 
					AND tbl_items.id_item NOT IN ($produits)
					AND tbl_items.id_type_item in (5,3)
				)
			)
	GROUP BY tbl_reactions_croisees.id_reaction_croisee
	";
	
	$res = spip_query($query);
	
	/* $reactif_avec : elements qui réagissent avec ceux du penta 
		chaque item contient un tableau d'éléments du penta avec lesquels il réagit
		*/
	
	/* Tri pour avoir un tableau associatif correct */
	while ($row = spip_fetch_array($res)){
		if (in_array($row['idp1'],$tableau_produits)) {
			$reactif_avec[$row['idp2']][$row['idp1']] = $row['idp1'];
			$t_id_suggestion_trouvee[$row['idp2']] = $row['idp2']; 
		}
		else {
			$reactif_avec[$row['idp1']][$row['idp2']] = $row['idp2'];
			$t_id_suggestion_trouvee[$row['idp1']] = $row['idp1']; 
		}
	}
	
	/* Recherche sur les parents et remontée des produits réactifs */
	foreach ($tableau_produits as $id_item) {
		$res = spip_query("
			SELECT id_item from tbl_est_dans
			WHERE est_dans_id_item = $id_item
		");
		while ($row = spip_fetch_array($res)){
			$id = $row['id_item'];
			$query = "
			SELECT tbl_items.id_item AS idp1, 
				tbl_items_1.id_item AS idp2
			FROM tbl_est_dans, tbl_est_dans AS tbl_est_dans_1, tbl_reactions_croisees, tbl_items, tbl_items AS tbl_items_1
			WHERE tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1
				AND tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2
				AND tbl_est_dans.id_item = tbl_items.id_item
				AND tbl_est_dans_1.id_item = tbl_items_1.id_item
				AND (tbl_reactions_croisees.fleche_sens1 = 1 OR tbl_reactions_croisees.fleche_sens1 = 1)
				AND 
					(
						( tbl_items.id_item IN ($id) 
							AND tbl_items_1.id_item NOT IN ($id)
							AND tbl_items_1.id_item NOT IN ($produits)
							AND tbl_items_1.id_type_item in (5,3)
						)
					OR
						(
							tbl_items_1.id_item IN ($id)
							AND tbl_items.id_item NOT IN ($id)
							AND tbl_items.id_item NOT IN ($produits)
							AND tbl_items.id_type_item in (5,3)
						)
					)
			GROUP BY tbl_reactions_croisees.id_reaction_croisee
			";
			
			$res2 = spip_query($query);
			
			/* Tri pour avoir un tableau associatif correct */
			/* astuce : on affecte au pere la RC trouvée depuis le fils */
			while ($row2 = spip_fetch_array($res2)){
				if ($row2['idp1'] == $id) {
					$reactif_avec[$row2['idp2']][$id_item] = $id_item;
					$t_id_suggestion_trouvee[$row2['idp2']] = $row2['idp2']; 
				}
				else {
					$reactif_avec[$row2['idp1']][$id_item] = $id_item;
					$t_id_suggestion_trouvee[$row2['idp1']] = $row2['idp1']; 
				}
			}

		}
	}
	
	/* On récupère le nom pour construire l'assemblage final */
	$res = spip_query("
		SELECT id_item, nom from tbl_items
		where id_item in (".implode(',',$t_id_suggestion_trouvee).")
		ORDER BY nom ASC
	");
	
	while ($row = spip_fetch_array($res)){
		$nb = sizeof($reactif_avec[$row['id_item']]);
		$t_suggestions[] = array(
				'nb' => $nb, 
				'nom' => $row['nom'].'==>['.implode(',',$reactif_avec[$row['id_item']]).']', 
				'id_mol' => $row['id_item']);
	}
	
	/* un tri sur le nombre puis on met tout à plat */
	$nb = $nom = $id_mol = $aFinal = array();
	
	foreach ($t_suggestions as $key => $row) {
	    $nb[$key]  = $row['nb'];
	    $nom[$key] = $row['nom'];
	    $id_mol[$key] = $row['id_mol'];
	}

	array_multisort($nb, SORT_DESC, $nom, SORT_ASC, $id_mol, SORT_ASC, $t_suggestions);
	
	return json_encode($t_suggestions);
	

}
?>