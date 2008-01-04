<?php
function rc($p1,$p2) {
	$tableau_produits = $items_fils_de = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	
	$produits = implode(",", $tableau_produits);
	
	foreach ($tableau_produits as $id_item_source) {
		$query = "SELECT tbl_items.id_item FROM tbl_items, tbl_est_dans 
			WHERE est_dans_id_item = $id_item_source
			AND tbl_est_dans.id_item = tbl_items.id_item";
		$res = spip_query($query);

		while($row = spip_fetch_array($res)) {
			$items_fils_de[$row['id_item']][] = $id_item_source;
		}
	}

	// Requete identique à action/liste_des_rc
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
				AND produits_differents = 1
				AND tbl_reactions_croisees.id_produit1 = tbi3.id_item
				AND tbl_reactions_croisees.id_produit2 = tbi4.id_item
			);";
	$res = spip_query($query);
	$result = '';
	$biblio = '';
	
	while ($row = spip_fetch_array($res)){
		
		// Trouver le parent pour tester si les 2 produits sont dans la meme famille
		if (((isset($items_fils_de[$row['idp1']])) && (isset($items_fils_de[$row['idp2']]))
				&& array_intersect($items_fils_de[$row['idp1']],$items_fils_de[$row['idp2']])) == false) {
			
			$querybiblio = "SELECT tbl_bibliographies.id_biblio, tbl_bibliographies.citation, 
								tbl_groupes_patients.id_groupe_patients, tbl_groupes_patients.pays, tbl_groupes_patients.description_groupe, tbl_groupes_patients.nb_sujets, tbl_groupes_patients.pool, tbl_groupes_patients.qualitatif,
								tbl_reactions_croisees.id_reaction_croisee, tbl_items.id_item as i1, tbl_items.nom as p1, tbl_reactions_croisees.niveau_RC_sens1, 
									tbl_reactions_croisees.niveau_RC_sens2, tbl_items_1.id_item as i2, tbl_items_1.nom as p2, tbl_reactions_croisees.remarques
								FROM tbl_items AS tbl_items_1 
									INNER JOIN (tbl_items 
										INNER JOIN ((tbl_reactions_croisees 
											INNER JOIN tbl_groupes_patients ON tbl_reactions_croisees.id_groupe_patients = tbl_groupes_patients.id_groupe_patients) 
											INNER JOIN tbl_bibliographies ON tbl_groupes_patients.id_biblio = tbl_bibliographies.id_biblio) 
										ON tbl_items.id_item = tbl_reactions_croisees.id_produit1) 
									ON tbl_items_1.id_item = tbl_reactions_croisees.id_produit2
							WHERE (((tbl_reactions_croisees.id_reaction_croisee)=".$row['id_reaction_croisee']."));";
							
			$resbiblio = spip_query($querybiblio);
			
			while ($rowbiblio = spip_fetch_array($resbiblio)){
				$linkbiblio = '<a href="#biblio'.$rowbiblio['id_biblio'].'">';
				$biblio .= '<a name="biblio'.$rowbiblio['id_biblio'].'" id="biblio'.$rowbiblio['id_biblio'].'"></a><table summary="D&eacute;tails des donn&eacute;es bibiliographiques" class="bibliographie spip"><caption>D&eacute;tails des travaux de r&eacute;activit&eacute; crois&eacute;e: '.$row['id_reaction_croisee'].'</caption><tbody>
								<tr><td colspan="5" rowspan="1">'.$rowbiblio['citation'].'</td></tr>
								<tr><td><b>Pays</b>: '.$rowbiblio['pays'].'</td><td colspan="4" rowspan="1">'.$rowbiblio['description_groupe'].'</td></tr>
								<tr><td><b>Nb sujets</b>: '.$rowbiblio['nb_sujets'].'</td><td colspan="2" rowspan="1"><b>S&eacute;rums pool&eacute;s</b>: '.(($rowbiblio['pool']==1)?'Oui':'Non').'</td><td colspan="2" rowspan="1"><b>Test qualitatif</b>: '.(($rowbiblio['qualitatif']==1)?'Oui':'Non').'</td></tr>
								<tr><td><b>Produit1</b></td><td><b>RC 1-&gt; 2</b></td><td><b>RC 2-&gt;1</b></td><td><b>Produit2</b></td><td><b>Remarques</b></td></tr>
								<tr><td>'.$rowbiblio['p2'].'</td><td>'.$rowbiblio['niveau_RC_sens2'].'</td><td>'.$rowbiblio['niveau_RC_sens1'].'</td><td>'.$rowbiblio['p1'].'</td><td>'.$rowbiblio['remarques'].'</td></tr>
								</tbody></table><a class="small right" href="#top">Retour au sommet</a><br class="nettoyeur"/>';
			}
			
			if ($p1 == $row['id_s1']) {
				$nrc1 = (($row['niveau_RC_sens1'] <> '') ? ' ('.$row['niveau_RC_sens1'].')' : '');
				$nrc2 = (($row['niveau_RC_sens2'] <> '') ? ' ('.$row['niveau_RC_sens2'].')' : '');
				$fl1 = (($row['fleche_sens1'] === '0') ? '<img src="squelettes/css/img/rc_jamais_lr.gif" alt="Jamais" title="Jamais'.$nrc1.'" />': (($row['fleche_sens1'] === '1') ? '<img src="squelettes/css/img/rc_toujours_lr.gif" alt="Toujours" title="Toujours'.$nrc2.'" />': '<span></span>'));
				$fl2 = (($row['fleche_sens2'] === '0') ? '<img src="squelettes/css/img/rc_jamais_rl.gif" alt="Jamais" title="Jamais'.$nrc2.'" />': (($row['fleche_sens2'] === '1') ? '<img src="squelettes/css/img/rc_toujours_rl.gif" alt="Toujours" title="Toujours'.$nrc1.'" />': '<span></span>'));

				$result .= '<tr><td align=left>'. $row['id_reaction_croisee'].'</td><td align=left><a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p2']).'\',\'?page=popup_item&amp;id_item='.$row['idp2'].'\'); return false">'.$row['p2'].' '.'</a></td><td>'.$linkbiblio.$fl1.'</a></td><td>'.$linkbiblio.$fl2.'</a></td><td align=left><a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p1']).'\',\'?page=popup_item&amp;id_item='.$row['idp1'].'\'); return false">'.$row['p1'].'</a></td></tr>';
			} else {
				$nrc1 = (($row['niveau_RC_sens2'] <> '') ? ' ('.$row['niveau_RC_sens2'].')' : '');
				$nrc2 = (($row['niveau_RC_sens1'] <> '') ? ' ('.$row['niveau_RC_sens1'].')' : '');
				$fl1 = (($row['fleche_sens2'] === '0') ? '<img src="squelettes/css/img/rc_jamais_rl.gif" alt="Jamais" title="Jamais'.$nrc2.'" />': (($row['fleche_sens2'] === '1') ? '<img src="squelettes/css/img/rc_toujours_rl.gif" alt="Toujours" title="Toujours'.$nrc1.'" />': '<span></span>'));
				$fl2 = (($row['fleche_sens1'] === '0') ? '<img src="squelettes/css/img/rc_jamais_lr.gif" alt="Jamais" title="Jamais'.$nrc1.'" />': (($row['fleche_sens1'] === '1') ? '<img src="squelettes/css/img/rc_toujours_lr.gif" alt="Toujours" title="Toujours'.$nrc2.'" />': '<span></span>'));

				$result .= '<tr><td align=left>'. $row['id_reaction_croisee'].'</td><td align=left><a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p1']).'\',\'?page=popup_item&amp;id_item='.$row['idp1'].'\'); return false">'.$row['p1'].' '.'</a></td><td>'.$linkbiblio.$fl2.'</a></td><td>'.$linkbiblio.$fl1.'</a></td><td align=left><a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p2']).'\',\'?page=popup_item&amp;id_item='.$row['idp2'].'\'); return false">'.$row['p2'].'</a></td></tr>';
			}


		}
		
	}
	if ($result) $result = '<div class="blocContenuArticle"><h1 class="titArticle">'._T('reactivites_croisees').'</h1><table class="liste_rc spip"><th>Biblio</th><th colspan="2">'.produit($p1).'</th><th colspan="2">'.produit($p2).'</th>'.$result.'</table></div>';
	if ($biblio) $result .= '<div class="blocContenuArticle"><h2 class="titArticle">'._T('ad:bibliographies').'</h2>'.$biblio.'</div>';
	return $result;
}

function biblio($id_biblio, $id_reaction_croisee) {
	if (!is_numeric($id_biblio)) return;
	
	$result = '';
	$querybiblio = "SELECT citation, annee
					FROM tbl_bibliographies
					WHERE (
						((id_biblio)=$id_biblio) 
						)";
	$resbiblio = spip_query($querybiblio);
	while ($rowbiblio = spip_fetch_array($resbiblio)){
		$biblio .= '<tr><td>'.$id_reaction_croisee.'</td><td><a name="biblio'.$id_biblio.'"></a>'.$rowbiblio['citation'].'</td><td>'.$rowbiblio['annee'].'</td>';
	}
	$result .= $biblio;

	return $result;
}

function produit($produits) {
	if (!is_numeric($produits)) return;
	
	$result = '';
	$queryproduit = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom
					FROM (tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.est_dans_id_item) 
						INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item
					WHERE (
						((tbl_items.id_item)=$produits) 
						AND ((tbl_items_1.id_type_item)=5)
						AND ( NOT ISNULL(tbl_items_1.nom))
						)
					ORDER BY tbl_items_1.nom;";
	$resproduit = spip_query($queryproduit);
	while ($rowproduit = spip_fetch_array($resproduit)){
		$produit .= $rowproduit['nom'];
	}
	$result .= $produit;

	return $result;
}
