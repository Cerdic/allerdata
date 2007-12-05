<?php
function rc($p1,$p2) {
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
			tbl_reactions_croisees.niveau_RC_sens1, 
			tbl_reactions_croisees.fleche_sens2, 
			tbl_reactions_croisees.niveau_RC_sens2, 
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
		$querybiblio = "Select tbl_groupes_patients.id_biblio FROM tbl_reactions_croisees INNER JOIN tbl_groupes_patients ON tbl_reactions_croisees.id_groupe_patients=tbl_groupes_patients.id_groupe_patients WHERE id_reaction_croisee=".$row['id_reaction_croisee'].";";
		$resbiblio = spip_query($querybiblio);
		while ($rowbiblio = spip_fetch_array($resbiblio)){
			$linkbiblio = '<a href="?page=popup_biblio&amp;id_biblio='. $rowbiblio['id_biblio'] .'">';
		}
		$nrc1 = (($row['niveau_RC_sens1'] <> '') ? ' ('.$row['niveau_RC_sens1'].')' : '');
		$nrc2 = (($row['niveau_RC_sens2'] <> '') ? ' ('.$row['niveau_RC_sens2'].')' : '');
		$fl1 = (($row['fleche_sens1'] === '0') ? '<img src="/squelettes/css/img/rc_jamais_rl.gif" alt="Jamais" title="Jamais'.$nrc1.'" />': (($row['fleche_sens1'] === '1') ? '<img src="/squelettes/css/img/rc_toujours_rl.gif" alt="Toujours" title="Toujours'.$nrc2.'" />': ''));
		$fl2 = (($row['fleche_sens2'] === '0') ? '<img src="/squelettes/css/img/rc_jamais_lr.gif" alt="Jamais" title="Jamais'.$nrc1.'" />': (($row['fleche_sens2'] === '1') ? '<img src="/squelettes/css/img/rc_toujours_lr.gif" alt="Toujours" title="Toujours'.$nrc2.'" />': ''));
		$result .= '<tr><td><!--'. $row['id_reaction_croisee'].'--><a href="?page=popup_item&amp;id_item='.$row['idp1'].'">'.$row['p1'].' '.'</a></td><td>'.$linkbiblio.$fl2.'</a></td><td>'.$linkbiblio.$fl1.'</a></td><td><a href="?page=popup_item&amp;id_item='.$row['idp2'].'">'.$row['p2'].'</a></td></tr>';
	}

	return $result;
}
