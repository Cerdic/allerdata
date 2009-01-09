<?php

function cmp($a, $b) 
/* fonction de tri inverse via uksort */
{
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function liste_des_suggestions($produits_penta) {
	$tableau_produits = $t_suggestions = $items_famille = $t_id_suggestion_trouvee = array();
	
	// par precaution, pour ne pas se faire injecter n'importe quoi
  $tableau_produits = array_map('intval',explode(',',$produits_penta));
	$produits_penta = implode(",", $tableau_produits);
	
	if (!count($tableau_produits)) return '[]';
	
	$famille_produits = penta_produits_exclus($tableau_produits);
	$produits = implode(",", $famille_produits);

 
	$tt = '';
	
	/* Liste des suggestions */
	$query = <<<EOQ
# Requete pour trouver les items a suggerer

(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1)
            # Ne prendre que les éléments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit2 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous intéresse)
    INNER JOIN tbl_items AS tbl_items_3 ON tbl_items_3.id_item = tbl_est_dans_1.id_item
    WHERE (
				    # RC avec id_produit1 comme fils des elements du penta, et id_produit2 qui n'est pas dans la famille
        tbl_est_dans.est_dans_id_item In ($produits_penta)
    		    # l'item est publie ...
    		AND tbl_items_3.statut='publie'
            # Le produit est contenu dans "bouleau Esp"
        AND tbl_items_3.id_item Not In ($produits)
            # La cible ne doit pas contenir le produit du pentagramme
        AND ((tbl_items_3.id_type_item)=5 Or (tbl_items_3.id_type_item)=13)
            # La cible est contenu dans un produit de type "produit" ou "espece" (c'est ce dernier qui nous interesse)
        AND (
					tbl_reactions_croisees.fleche_sens1=1
					OR
					tbl_reactions_croisees.fleche_sens2=1
				)
				AND tbl_reactions_croisees.statut='publie'
    )
)	

UNION

# Sens inverse : on prend tous ceux qui pointent (en rc->id_produit2) vers un fils des produits du penta
(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit2)
            # Ne prendre que les éléments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit1 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous intéresse)
    INNER JOIN tbl_items AS tbl_items_3 ON tbl_items_3.id_item = tbl_est_dans_1.id_item
    WHERE (
				# RC avec id_produit2 comme fils des elements du penta, et id_produit1 qui n'est pas dans la famille
        tbl_est_dans.est_dans_id_item In ($produits_penta)
    		    # l'item est publie ...
    		AND tbl_items_3.statut='publie'
            # Le produit est contenu dans "bouleau Esp"
        AND tbl_items_3.id_item Not In ($produits)
            # La cible ne doit pas contenir le produit du pentagramme
        AND ((tbl_items_3.id_type_item)=5 Or (tbl_items_3.id_type_item)=13)
            # La source est un produit de type "produit" ou "espèce" (c'est ce dernier qui nous intéresse)
        AND (
					tbl_reactions_croisees.fleche_sens1=1
					OR
					tbl_reactions_croisees.fleche_sens2=1
				)
				AND tbl_reactions_croisees.statut='publie'
    )
)	


EOQ;


 	$res = spip_query($query);
	while ($row = sql_fetch($res)){
		if (!isset($t_suggestions[$row['id_item']])) {
			$t_suggestions[$row['id_item']] = array(
					'nom' => (($row['nom_court']=='')?$row['nom']:$row['nom_court']),
					'source' => sql_getfetsel("nom","tbl_items","id_item=".intval(penta_ascendant_le_plus_proche($row['id_item'],'source'))),
					'id_mol' => $row['id_item']);
		}
    $reactif_avec[$row['id_item']][$row['id_item_penta']] = $row['id_item_penta'];
	}
	
	if (_request('debug')) {echo "
	/* REACTIF AVEC : <pre>
	";
	var_dump($reactif_avec);
	echo " </pre><hr>
	 */       

	";}

	foreach($t_suggestions as $id_item => $sugg) {
		$nb = sizeof($reactif_avec[$id_item]);
    if ($nb) {
  		$t_suggestions[$id_item]['nb'] = $nb;
  		$t_suggestions[$id_item]['items_actifs'] = '['.implode(',',$reactif_avec[$id_item]).']';
    } else {
			unset($t_suggestions[$id_item]);
		}
	}

	/* un tri sur le nombre puis on met tout a plat */
	$nb = $nom = $id_mol = $aFinal = array();
	
	foreach ($t_suggestions as $key => $row) {
	    $nb[$key]  = $row['nb'];
	    $nom[$key] = $row['nom'];
	    $id_mol[$key] = $row['id_mol'];
	}
  $item = array_map('strtolower', $nom);

	array_multisort($nb, SORT_DESC, $item, SORT_ASC, $t_suggestions);
	
	if (_request('debug')) {
	echo "
	/* SUGGESTIONS : <pre>
	";
	var_dump($t_suggestions);
	echo " </pre><hr>
	 */       

	";      
		
	}

  $suggestion = json_encode($t_suggestions);
  
	return $suggestion;
}
?>