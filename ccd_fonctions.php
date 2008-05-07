<?php


// Reprendre la requete dans une iframe avec les parametres (comme les popups)
// Ou charger le bloc en Ajax (permet de garder le données)
// Voir l'iframe ne permet de pas de érer l'historique.. (normaeme,t c'est avec le hash)

// Donc meme requete, avec le CCD en plus
// .. On calcule un chiffre en SPIP misez en forme (en image cliquable)
// Ajouter une zone cliquable (bulette) pour afficher une autre page/popup

// le clic appelle une fonction dans le parent, qui va effectuer la mise en avant des éléments concernés (idem pour la popup)


function ccd($txt) {
	$tableau_produits = array();
	
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[1] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[2] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[3] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[4] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[5] = $_REQUEST['p5'];
	
	if (!sizeof($tableau_produits)) return '';
	
  // le tuple est ordonné 
  $signature = sprintf('%d,%d,%d,%d,%d', 
                      $tableau_produits[1],
                      $tableau_produits[2],
                      $tableau_produits[3],
                      $tableau_produits[4],
                      $tableau_produits[5]
                      );
  $q = spip_query("select resultat_json from cache_requetes where tuple='".mysql_real_escape_string($signature)."' and page='ccd'");
  if (spip_num_rows($q)) {
    $r = spip_fetch_array($q);
    return ($r['resultat_json']);
  }

	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
  /* ALGO : on recherche les familles moléculaires (F) dont font partie certains éléments (A) ayant le champ "CCD possible" égal à "1"
	 * qui eux-même sont contenus dans certains produits (P) du penta. 
	 * Les produits (P) du penta sont mis en avant. la valeur affichée est le nombre de (P).
	 */
  $query = "SELECT DISTINCT tbl_items_2.id_item, tbl_items_2.nom AS produit
		FROM (((tbl_items INNER JOIN tbl_est_dans ON tbl_items.id_item = tbl_est_dans.id_item) 
			INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_items.id_item = tbl_est_dans_1.id_item) 
			INNER JOIN tbl_items AS tbl_items_1 ON tbl_est_dans_1.est_dans_id_item = tbl_items_1.id_item) 
			INNER JOIN tbl_items AS tbl_items_2 ON tbl_est_dans.est_dans_id_item = tbl_items_2.id_item
		WHERE (((tbl_items_2.id_item) IN ($produits)) AND ((tbl_items_1.id_type_item)=5) AND ((tbl_items.ccd_possible)=1))
		";
			
	$liste_prod_ccd = array();
	$nb_ccd = 0;
	
	$res = spip_query($query);
		
	while ($row = spip_fetch_array($res)){
		$liste_prod_ccd[$row['id_item']] = $row['id_item'];
	}
	$nb_ccd = sizeof($liste_prod_ccd);
	
	$js_liste_prod_ccd = '['.implode(',',$liste_prod_ccd).']';
	$output = "<a href='#' class='outlineLink' onclick='CCD.outline_prod($js_liste_prod_ccd);return false;'>".$nb_ccd."</a>";

  // On stocke pour un prochain appel
  spip_query("INSERT INTO cache_requetes (page,tuple,resultat_json,date_maj) 
              VALUES('ccd',
                     '".mysql_real_escape_string($signature)."',
                     '".mysql_real_escape_string($output)."',
                     NOW())");
  return $output;
}
?>