<?php

	if (!defined("_ECRIRE_INC_VERSION")) return;

	$tableau_produits = array();
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

	$produits = implode(",", $tableau_produits);
	
	// Requete de recherche : il faut tenir compte des réactions entre éléments de type 3 ou 5
	$query = "SELECT DISTINCT 
			tbl_reactions_croisees.id_reaction_croisee, 
			tbl_items.id_item AS idp1, 
			tbl_items.nom AS p1, 
			tbl_reactions_croisees.id_produit1,
			tbi3.id_type_item AS id_type_item1, 
			tbl_reactions_croisees.fleche_sens1, 
			tbl_reactions_croisees.niveau_RC_sens1, 
			tbl_reactions_croisees.fleche_sens2, 
			tbl_reactions_croisees.niveau_RC_sens2, 
			tbi4.id_type_item AS id_type_item2, 
			tbl_reactions_croisees.id_produit2, 
			tbl_items_1.id_item AS idp2, 
			tbl_items_1.nom AS p2, 
			tbl_reactions_croisees.produits_differents, 
			tbl_est_dans.est_dans_id_item AS id_s1, 
			tbl_est_dans_1.est_dans_id_item AS id_s2
		FROM tbl_items as tbi3, tbl_items as tbi4, ((((tbl_est_dans AS tbl_est_dans_1 INNER JOIN (tbl_reactions_croisees INNER JOIN tbl_est_dans ON tbl_reactions_croisees.id_produit1 = tbl_est_dans.id_item) ON tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2) INNER JOIN tbl_items ON tbl_est_dans_1.id_item = tbl_items.id_item) INNER JOIN tbl_types_items ON tbl_items.id_type_item = tbl_types_items.id_type_item) INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) INNER JOIN tbl_types_items AS tbl_types_items_1 ON tbl_items_1.id_type_item = tbl_types_items_1.id_type_item
		WHERE (
				((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_est_dans_1.est_dans_id_item) In ($produits))
				AND
				(produits_differents = 1)
				AND tbl_reactions_croisees.id_produit1 = tbi3.id_item
				AND tbl_reactions_croisees.id_produit2 = tbi4.id_item
			);";
	
	$res = spip_query($query);
	// $arc contient les arcs orientés 
	// $arc[a][b] correspond à l'arc de a vers b
	$arc = array();
	
	while ($row = spip_fetch_array($res)){
		
		$id_s1 = $row['id_s1'];
		$id_s2 = $row['id_s2'];

		if ($row['fleche_sens1'] != '') {
			if (!isset($arc[$id_s1])) $arc[$id_s1] = array();
			if (!isset($arc[$id_s1][$id_s2])) {
				if (($row['fleche_sens1'] != 0) || in_array($row['id_type_item1'],array(3,5))) {
					$arc[$id_s1][$id_s2] = $row['fleche_sens1'];
				}
			} else {
				if (($row['fleche_sens1'] != 0) || in_array($row['id_type_item1'],array(3,5))) {
					$arc[$id_s1][$id_s2] = ($row['fleche_sens1'] + $arc[$id_s1][$id_s2]) /2;
				}
			}
		}

		if ($row['fleche_sens2'] != '') {
			if (!isset($arc[$id_s2])) $arc[$id_s2] = array();
			if (!isset($arc[$id_s2][$id_s1])) {
				if (($row['fleche_sens2'] != 0) || in_array($row['id_type_item2'],array(3,5))) {
					$arc[$id_s2][$id_s1] = $row['fleche_sens2'];
				}
			} else {
				if (($row['fleche_sens2'] != 0) || in_array($row['id_type_item2'],array(3,5))) {
					$arc[$id_s2][$id_s1] = ($row['fleche_sens2'] + $arc[$id_s2][$id_s1]) /2;
				}
			}
		}
	}
	
	$liste_des_rc = array();
	
	// Affectation des classes
	// 1 correspond à 'toujours'
	// 0 à 'jamais'
	// entre les 2 c'est 'discordant'
	foreach ($arc as $origine => $liens) {
		foreach ($liens as $destination => $valeur) {
			
			if ($valeur == 1) $className = 'rc_toujours';
			elseif ($valeur == 0) $className = 'rc_jamais';
			else $className = 'rc_discordant';
			
			$liste_des_rc[] = array('source' => $origine, 'dest' => $destination, 'classe' => $className);
		}
	}
	
	die('{liste_des_rc:'.json_encode($liste_des_rc).'}');
	