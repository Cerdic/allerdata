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
	$tableau_produits = $suggestion = array();
	$prem = array(17,97); /* 2 nombres premiers avant 100 avec un delta de 1/8 */
	
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
	
	/* Sens Aller : p1 vers p2 */
	/* Les produits connus sont les produits source */
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
			IN ($produits)
			AND tbl_items_1.id_type_item in (5,3) ". 
	//	AND tbl_items_1.affichage_suggestion =1
	"GROUP BY tbl_reactions_croisees.id_reaction_croisee
	ORDER BY tbl_items.nom
	";
		
	$res = spip_query($query);
	$liste = $suggestions = $nom = array();
		
	/* On stocke de façon à avoir "en premier" les produits suggérés */
	/* D'où la permutation -- qui ne sert qu'a l'affichage */
	while ($row = spip_fetch_array($res)){
			
		$nom[$row['idp2']] = $row['p2'];
		$nom[$row['idp1']] = $row['p1'];

		/* 3 cas possibles : NULL, 0 ou 1  */
		/* Astuce pour retrouver les discordants: 
		  utiliser les nombres premiers */

		if (!isset($liste[$row['idp2']])) $liste[$row['idp2']] = array();
		if (!isset($liste[$row['idp2']][$row['idp1']])) 
			$liste[$row['idp2']][$row['idp1']] = array();
		if (!isset($liste[$row['idp2']][$row['idp1']])) {
			$s1 = $s2 = 0;
			if (!is_null($row['fleche_sens1'])) $s2 = $prem[$row['fleche_sens1']];
			if (!is_null($row['fleche_sens2'])) $s1 = $prem[$row['fleche_sens2']];
			$liste[$row['idp2']][$row['idp1']] = array('s1' => $s1,'s2' => $s2);
		}
		else {
			$s1 = $liste[$row['idp2']][$row['idp1']]['s1'];
			$s2 = $liste[$row['idp2']][$row['idp1']]['s2'];
			if (!is_null($row['fleche_sens1'])) $s2 += $prem[$row['fleche_sens1']];
			if (!is_null($row['fleche_sens2'])) $s1 += $prem[$row['fleche_sens2']];
			$liste[$row['idp2']][$row['idp1']] = array('s1' => $s1,'s2' => $s2);
		}
		
	}
	
	/* Sens Retour : p2 vers p1 */
	/* On permute les extrémités des RC pour la recherche,
		en indiquant que ce sont les produits cibles qui sont concernés */

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
		AND tbl_items_1.id_item
			IN ($produits ) 
		AND tbl_items.id_item NOT 
			IN ($produits)
			AND tbl_items.id_type_item in (5,3) ". 
	//	AND tbl_items.affichage_suggestion =1
	"GROUP BY tbl_reactions_croisees.id_reaction_croisee
	ORDER BY tbl_items_1.nom
	";
		
	/* Par contre il faut le stocker dans le tableau dans l'ordre inverse */
	$res = spip_query($query);
	while ($row = spip_fetch_array($res)){
			
		$nom[$row['idp1']] = $row['p1'];
		$nom[$row['idp2']] = $row['p2'];
		
		/* 3 cas possibles : NULL, 0 ou 1  */
		/* Astuce pour retrouver les discordants: 
		  utiliser les nombres premiers */
		
		if (!isset($liste[$row['idp1']])) $liste[$row['idp1']] = array();
		if (!isset($liste[$row['idp1']][$row['idp2']])) 
			$liste[$row['idp1']][$row['idp2']] = array();
		
		if (!isset($liste[$row['idp1']][$row['idp2']])) {
			$s1 = $s2 = 0;
			if (!is_null($row['fleche_sens1'])) $s1 = $prem[$row['fleche_sens1']];
			if (!is_null($row['fleche_sens2'])) $s2 = $prem[$row['fleche_sens2']];
			$liste[$row['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		else {
			$s1 = $liste[$row['idp1']][$row['idp2']]['s1'];
			$s2 = $liste[$row['idp1']][$row['idp2']]['s2'];
			if (!is_null($row['fleche_sens1'])) $s1 += $prem[$row['fleche_sens1']];
			if (!is_null($row['fleche_sens2'])) $s2 += $prem[$row['fleche_sens2']];
			$liste[$row['idp1']][$row['idp2']] = array('s1' => $s1,'s2' => $s2);
		}
		
	}

	// Ventilation 
	foreach ($liste as $idp1 => $l_idp1) {
		foreach ($l_idp1 as $idp2 => $l_idp2) {
			if (!$suggestion[$idp1]) {
				$suggestion[$idp1] = array('nom' => $nom[$idp1], 'nb' => 0, 'id_mol' => $idp1, 'reactivite' => 0);
			}
			$suggestion[$idp1]['nb'] += 1;
			$suggestion[$idp1]['reactivite'] += $l_idp2['s1'] + $l_idp2['s2'];
		}
	}

	/* Ensuite on ordonne la liste des suggestions par "réactivité" inverse, en utilisant notre pondération */
	uksort($suggestion,"cmp");
	
	/* On prépare un tri par nombre d'éléments concernés */
	$aNbProd = array();
	foreach ($suggestion as $s) {
		if (!isset($aNbProd[$s['nb']])) $aNbProd[$s['nb']] = array();
		$aNbProd[$s['nb']][] = $s;
	}
	uksort($aNbProd,"cmp");

	/* Maintenant il faut se "débarasser des index" : il ne faut pas retourner de tableau associatif */
	$aFinal = array();
	foreach ($aNbProd as $aProd) {
		foreach ($aProd as $s) {
			$aFinal[] = $s;
		}
	}
	
	return json_encode($aFinal);
	

}
?>