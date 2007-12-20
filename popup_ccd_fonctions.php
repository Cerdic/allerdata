<?php


// Reprendre la requete dans une iframe avec les parametres (comme les popups)
// Ou charger le bloc en Ajax (permet de garder le donn�es)
// Voir l'iframe ne permet de pas de �rer l'historique.. (normaeme,t c'est avec le hash)

// Donc meme requete, avec le CCD en plus
// .. On calcule un chiffre en SPIP misez en forme (en image cliquable)
// Ajouter une zone cliquable (bulette) pour afficher une autre page/popup

// le clic appelle une fonction dans le parent, qui va effectuer la mise en avant des �l�ments concern�s (idem pour la popup)


function ccd($p1,$p2,$p3,$p4,$p5) {
	$tableau_produits = array();
	if (is_numeric($p1)) $tableau_produits[] = $p1; 
	if (is_numeric($p2)) $tableau_produits[] = $p2;
	if (is_numeric($p3)) $tableau_produits[] = $p3;
	if (is_numeric($p4)) $tableau_produits[] = $p4;
	if (is_numeric($p5)) $tableau_produits[] = $p5;
	
	if (!sizeof($tableau_produits)) return;
	
	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	$query = "SELECT DISTINCT tbl_items_1.id_item, tbl_items_1.nom, tbl_est_dans.est_dans_id_item, tbl_items.id_item AS id_mol, tbl_items.nom AS nom2, tbl_items.glyco, tbl_items_2.nom AS produit
				FROM (((tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
					INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_items.id_item = tbl_est_dans_1.id_item) 
					INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans_1.est_dans_id_item = tbl_items_1.id_item) 
					INNER JOIN tbl_items AS tbl_items_2 ON tbl_est_dans.est_dans_id_item = tbl_items_2.id_item
		WHERE (((tbl_est_dans.est_dans_id_item) In ($produits)) AND ((tbl_items_1.id_type_item)=6))
		ORDER BY tbl_items_1.nom, tbl_est_dans.est_dans_id_item DESC;";
			
	/* --
	Algo � mettre en oeuvre : Si pour une des lignes renvoy�es, glyco vaut "-1" (c'est bizarement un champ texte), alors :
	 - m�moriser id_item concern� (la famille mol�culaire), est_dans_id_item (le produit dans le pentacle)
	 - +1 au nombre de CCD trouv�s
	 Afficher qu'il y a des CCD, leur nombre, au clic, surligner les familles mol�culaires concern�es, et les produits concern�s.
	 Au (i), appeler un popup qui va afficher la liste des mol�cules pr�cises (passer en param�tre au squelette les 5 produits choisis)
	-- */
	
	$liste_prod_ccd = '';
	$nb_ccd = 0;
	
	$res = spip_query($query);
		
	while ($row = spip_fetch_array($res)){
		$pos += 1;
	
		// CCD
		if ($row['glyco'] == '-1') {
			$nb_ccd ++;
			$liste_prod_ccd .= '<li>'.$row['nom'].' ('.$row['nom2'].') pr&eacute;sent dans: <b>'.$row['produit'].'</b></li>';
		}
	}
	
	return $liste_prod_ccd;
}
?>