<?php
session_start();
function rc($p1,$p2,$type_etude) {
	$tableau_produits = $items_fils_de = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	
  // on variable 'reset' permet de réinitialiser les sessions
  // qui sont retournées immédiatement (lorsqu'elles existent)
  if (isset($_REQUEST['reset'])) {
    if (isset($_SESSION['rc_' . $_REQUEST['reset']]))
      unset($_SESSION['rc_' . $_REQUEST['reset']]);
  } else {
    if (isset($_SESSION['rc_' . $type_etude]) && $_SESSION['rc_' . $type_etude])
      return ($_SESSION['rc_' . $type_etude]);
  }
  
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

	// Requete identique a action/liste_des_rc
	$query = "SELECT DISTINCT 
			tbl_reactions_croisees.id_reaction_croisee, 
			tbl_items.id_item AS idp1, 
			tbl_items.nom AS p1, 
			tbl_items.id_type_item AS type1,
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
			tbl_items_1.id_type_item AS type2,
      tbl_reactions_croisees.produits_differents, 
			tbl_est_dans.est_dans_id_item AS id_s1, 
			tbl_est_dans_1.est_dans_id_item AS id_s2
		FROM tbl_items as tbi3, tbl_items as tbi4, ((((tbl_est_dans AS tbl_est_dans_1 INNER JOIN (tbl_reactions_croisees INNER JOIN tbl_est_dans ON tbl_reactions_croisees.id_produit1 = tbl_est_dans.id_item) ON tbl_est_dans_1.id_item = tbl_reactions_croisees.id_produit2) INNER JOIN tbl_items ON tbl_est_dans_1.id_item = tbl_items.id_item) INNER JOIN tbl_types_items ON tbl_items.id_type_item = tbl_types_items.id_type_item) INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans.id_item = tbl_items_1.id_item) INNER JOIN tbl_types_items AS tbl_types_items_1 ON tbl_items_1.id_type_item = tbl_types_items_1.id_type_item
		WHERE (
				((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_est_dans_1.est_dans_id_item) In ($produits))
				
				AND tbl_reactions_croisees.id_produit1 = tbi3.id_item
				AND tbl_reactions_croisees.id_produit2 = tbi4.id_item
			)";
	
  switch ($type_etude) {
    case 'pp' :
      $query .= "AND (tbl_items.id_type_item IN (5,3,13)) AND (tbl_items_1.id_type_item IN (5,3,13))";
    break;
    case 'pa' :
      $query .= "AND (
                  ((tbl_items.id_type_item IN (5,3,13)) AND (tbl_items_1.id_type_item NOT IN (5,3,13)))
                  OR ((tbl_items.id_type_item NOT IN (5,3,13)) AND (tbl_items_1.id_type_item IN (5,3,13)))
                )";
    break;
    case 'aa' :
      $query .= "AND (tbl_items.id_type_item NOT IN (5,3,13)) AND (tbl_items_1.id_type_item NOT IN (5,3,13))";
    break;
  }

  $res = spip_query($query);
	$result = '';
	$biblio = '';
	$count = 0;
	$premiere_ligne = true; 
  
  if (!spip_num_rows($res)) {
    $_SESSION['rc_' . $type_etude] = '';
		if (isset($_REQUEST['reset']))
	    return 0; // Le prochain appel retournera le contenu stocké en session
		else
    	return "<div id='main'><h1 class='titArticle'>"._T('ad:aucune_etude_de_ce_type')."</h1></div>";
  } 
    
	while ($row = spip_fetch_array($res)){
		// Trouver le parent pour tester si les 2 produits sont dans la meme famille
		if (((isset($items_fils_de[$row['idp1']])) && (isset($items_fils_de[$row['idp2']]))
				&& array_intersect($items_fils_de[$row['idp1']],$items_fils_de[$row['idp2']])) == false) {
			
			$count += 1;
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
				$linkbiblio = '<a href="#biblio'.$row['id_reaction_croisee'].'">';
				if (!$premiere_ligne) $biblio .= '<tr><td colspan="5">&nbsp;</td></tr>';
				$premiere_ligne = false;
				$biblio .= '<tr class="row_first"><th colspan="5"><a name="biblio'.$row['id_reaction_croisee'].'" id="biblio'.$row['id_reaction_croisee'].'"></a><span class="left">'.$row['id_reaction_croisee'].' : </span><a class="small right" href="#top_'.$type_etude.'">Retour &agrave; la synth&egrave;se <img src="squelettes/img/arrow_up.gif" style="margin-bottom:-2px"/></a></th></tr>
								<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td colspan="5" rowspan="1">'.$rowbiblio['citation'].'</td></tr>
								<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td><b>Pays</b>: '.$rowbiblio['pays'].'</td><td colspan="4" rowspan="1">'.$rowbiblio['description_groupe'].'</td></tr>
								<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td><b>Nb sujets</b>: '.$rowbiblio['nb_sujets'].'</td><td colspan="2" rowspan="1"><b>S&eacute;rums test&eacute;s individuellement</b>: '.(($rowbiblio['pool']==1)?'Non':'Oui').'</td><td colspan="2" rowspan="1"><b>Test quantitatif</b>: '.(($rowbiblio['qualitatif']==1)?'Non':'Oui').'</td></tr>
								<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td><b>Produit1</b></td><td><b>RC 1-&gt; 2</b></td><td><b>RC 2-&gt;1</b></td><td><b>Produit2</b></td><td><b>Remarques</b></td></tr>
								<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><td>'.$rowbiblio['p1'].'</td><td>'.$rowbiblio['niveau_RC_sens1'].'</td><td>'.$rowbiblio['niveau_RC_sens2'].'</td><td>'.$rowbiblio['p2'].'</td><td>'.$rowbiblio['remarques'].'</td></tr>
								';
			}
			
			$flag_no_color = ($type_etude == 'pp')?'':'_nb';
      if (!in_array($row['type2'],array(7,8,9,10,13))) $link2 = '<a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p2']).'\',\'?page=popup_item&amp;id_item='.$row['idp2'].'\'); return false">'.$row['p2'].' '.'</a>';
      else $link2 = $row['p2'];
      if (!in_array($row['type1'],array(7,8,9,10,13))) $link1 = '<a href="#" onclick="main_panel.updateTab(null,\''.addslashes($row['p1']).'\',\'?page=popup_item&amp;id_item='.$row['idp1'].'\'); return false">'.$row['p1'].'</a>';
			else $link1 = $row['p1'];
      if ($p1 == $row['id_s1']) {
				$fl1 = (($row['fleche_sens1'] === '0') ? '<img src="squelettes/css/img/rc_jamais_lr'.$flag_no_color.'.gif" alt="" title="" />': (($row['fleche_sens1'] === '1') ? '<img src="squelettes/css/img/rc_toujours_lr'.$flag_no_color.'.gif" alt="" title="" />': '<span></span>'));
				$fl2 = (($row['fleche_sens2'] === '0') ? '<img src="squelettes/css/img/rc_jamais_rl'.$flag_no_color.'.gif" alt="" title="" />': (($row['fleche_sens2'] === '1') ? '<img src="squelettes/css/img/rc_toujours_rl'.$flag_no_color.'.gif" alt="" title="" />': '<span></span>'));
        $result .= '<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><th>'.$linkbiblio. $row['id_reaction_croisee'].'</a></th><td>'.$link2.'</td><td style="text-align:center; width:70px">'.$fl1.'</a></td><td style="text-align:center; width:70px">'.$fl2.'</a></td><td>'.$link1.'</td></tr>';
			} else {
				$fl1 = (($row['fleche_sens1'] === '0') ? '<img src="squelettes/css/img/rc_jamais_rl'.$flag_no_color.'.gif" alt="" title="" />': (($row['fleche_sens1'] === '1') ? '<img src="squelettes/css/img/rc_toujours_rl'.$flag_no_color.'.gif" alt="" title="" />': '<span></span>'));
				$fl2 = (($row['fleche_sens2'] === '0') ? '<img src="squelettes/css/img/rc_jamais_lr'.$flag_no_color.'.gif" alt="" title="" />': (($row['fleche_sens2'] === '1') ? '<img src="squelettes/css/img/rc_toujours_lr'.$flag_no_color.'.gif" alt="" title="" />': '<span></span>'));
				$result .= '<tr'.((($count % 2) == 0)?' class="row_even"':' class="row_odd"').'><th>'.$linkbiblio.$row['id_reaction_croisee'].'</a></th><td>'.$link1.'</td><td style="text-align:center; width:70px">'.$fl2.'</a></td><td style="text-align:center; width:70px">'.$fl1.'</a></td><td>'.$link2.'</td></tr>';
			}


		}
		
	}
	if ($result) {
		$result = '<div class="blocContenuArticle"><a name="top_'.$type_etude.'" id="top_'.$type_etude.'"></a><h1 class="titArticle">'._T('ad:titre_synthese_popup_rc').'</h1><table class="spip" width=100%><thead><tr class="row_first"><th style="width:50px;">Biblio</th><th style="width:285px" colspan="2">'.produit($p1).'</th><th style="width:325px" colspan="2">'.produit($p2).'</th></tr></thead><tbody>'.$result.'</tbody></table></div>';
		if ($biblio) $result .= '<div class="blocContenuArticle"><h2 class="titArticle">'._T('ad:titre_bibliographies_popup_rc').'</h2><table summary="D&eacute;tails des donn&eacute;es bibiliographiques" class="bibliographie spip"><tbody>'.$biblio.'</tbody></table><br class="nettoyeur"/></div>';
    $_SESSION['rc_' . $type_etude] = '<div id="main">'.$result.'</div>';
  } else
		$result = "<h1 class='titArticle'>"._T('ad:aucune_etude_de_ce_type')."</h1>";
		
	if (isset($_REQUEST['reset']))
    return $count; // Le prochain appel retournera le contenu stocké en session
	else
		return "<div id='main'>".$result."</div>";
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
	$queryproduit = "SELECT nom
					FROM tbl_items
					WHERE (
						((tbl_items.id_item)=$produits) 
						);";
	$resproduit = spip_query($queryproduit);
	while ($rowproduit = spip_fetch_array($resproduit)){
		$produit .= $rowproduit['nom'];
	}
	$result .= $produit;

	return $result;
}
