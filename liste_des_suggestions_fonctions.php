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
	$tableau_produits = $t_suggestions = $items_famille = $t_id_suggestion_trouvee = array();
	
	if (isset($_REQUEST['p1']) && is_numeric($_REQUEST['p1'])) $tableau_produits[] = $_REQUEST['p1']; 
	if (isset($_REQUEST['p2']) && is_numeric($_REQUEST['p2'])) $tableau_produits[] = $_REQUEST['p2'];
	if (isset($_REQUEST['p3']) && is_numeric($_REQUEST['p3'])) $tableau_produits[] = $_REQUEST['p3'];
	if (isset($_REQUEST['p4']) && is_numeric($_REQUEST['p4'])) $tableau_produits[] = $_REQUEST['p4'];
	if (isset($_REQUEST['p5']) && is_numeric($_REQUEST['p5'])) $tableau_produits[] = $_REQUEST['p5'];
	
	if (!sizeof($tableau_produits)) return '[]';
	
	session_start();
	// M�moriser les produits du penta
	$_SESSION['produits_choisis'] = $tableau_produits;
	// ainsi que leurs parents
	if ($tableau_produits) {
		$res = spip_query("select DISTINCT id_item from tbl_est_dans where est_dans_id_item IN (".implode(',',$tableau_produits).")");
		while ($row = spip_fetch_array($res)){
			$_SESSION['produits_choisis'][] = $row['id_item'];
		}
	}
	session_write_close();

	$produits = implode(",", $tableau_produits);
	
	$tt = '';
	
	/* Liste des suggestions */
	$query = <<<EOQ
# Requ�te pour trouver les items � sugg�rer

(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.source, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1)
            # Ne prendre que les �l�ments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit2 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous int�resse)
    INNER JOIN tbl_items AS tbl_items_3 ON tbl_items_3.id_item = tbl_est_dans_1.id_item
    WHERE (
        ((tbl_est_dans.est_dans_id_item) In ($produits))
            # Le produit est contenu dans "bouleau Esp"
        AND ((tbl_est_dans_1.est_dans_id_item) Not In (
            SELECT distinct  tbl_items_1.id_item
            FROM tbl_items AS tbl_items_1
            INNER JOIN tbl_est_dans
                ON tbl_items_1.id_item = tbl_est_dans.est_dans_id_item
            WHERE (
            ((tbl_est_dans.id_item) In ($produits))))
        )
            # La cible ne doit pas contenir le produit du pentagramme
        AND ((tbl_items_3.id_type_item)=5 Or (tbl_items_3.id_type_item)=13)
            # La cible est contenu dans un produit de type "produit" ou "esp�ce" (c'est ce dernier qui nous int�resse)
        AND ((tbl_reactions_croisees.fleche_sens1)=1)
            # On est dans une relation source vers cible
    )
    OR(
        ((tbl_est_dans.est_dans_id_item) In ($produits))
        AND ((tbl_items_3.id_item) Not In (
            SELECT distinct  tbl_items_1.id_item
            FROM tbl_items AS tbl_items_1
            INNER JOIN tbl_est_dans
                ON tbl_items_1.id_item = tbl_est_dans.est_dans_id_item
            WHERE (
                ((tbl_est_dans.id_item) In ($produits))))
        )
        AND ((tbl_items_3.id_type_item)=5 OR (tbl_items_3.id_type_item)=13)
        AND ((tbl_reactions_croisees.fleche_sens2)=1)
            # Idem que la premi�re condition, mais on est dans une relation inverse cible vers source
    )
)	

UNION

(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.source, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit2)
            # Ne prendre que les �l�ments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit1 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous int�resse)
    INNER JOIN tbl_items AS tbl_items_3 ON tbl_items_3.id_item = tbl_est_dans_1.id_item
    WHERE (
        ((tbl_est_dans.est_dans_id_item) In ($produits))
            # Le produit est contenu dans "bouleau Esp"
        AND ((tbl_est_dans_1.est_dans_id_item) Not In (
            SELECT distinct  tbl_items_1.id_item
            FROM tbl_items AS tbl_items_1
            INNER JOIN tbl_est_dans
                ON tbl_items_1.id_item = tbl_est_dans.est_dans_id_item
            WHERE (
            ((tbl_est_dans.id_item) In ($produits))))
        )
            # La cible ne doit pas contenir le produit du pentagramme
        AND ((tbl_items_3.id_type_item)=5 Or (tbl_items_3.id_type_item)=13)
            # La cible est contenu dans un produit de type "produit" ou "esp�ce" (c'est ce dernier qui nous int�resse)
        AND ((tbl_reactions_croisees.fleche_sens1)=1)
            # On est dans une relation source vers cible
    )
    OR(
        ((tbl_est_dans.est_dans_id_item) In ($produits))
        AND ((tbl_est_dans_1.est_dans_id_item) Not In (
            SELECT distinct  tbl_items_1.id_item
            FROM tbl_items AS tbl_items_1
            INNER JOIN tbl_est_dans
                ON tbl_items_1.id_item = tbl_est_dans.est_dans_id_item
            WHERE (
                ((tbl_est_dans.id_item) In ($produits))))
        )
        AND ((tbl_items_3.id_type_item)=5 OR (tbl_items_3.id_type_item)=13)
        AND ((tbl_reactions_croisees.fleche_sens2)=1)
            # Idem que la premi�re condition, mais on est dans une relation inverse cible vers source
    )
)	

# Il faut pr�voir les r�actions crois�es qui de sont QUE dans le sens inverse



EOQ;

 	$res = spip_query($query);
	
	while ($row = spip_fetch_array($res)){
		if (!isset($t_suggestions[$id_item])) {
			$t_suggestions[$row['id_item']] = array(
					'nom' => (($row['nom_court']=='')?$row['nom']:$row['nom_court']),
					'source' => $row['source'],
					'id_mol' => $row['id_item']);
		}
    $reactif_avec[$row['id_item']][$row['id_item_penta']] = $row['id_item_penta'];
	}
	
	foreach($t_suggestions as $id_item => $sugg) {
		$nb = sizeof($reactif_avec[$id_item]);
		$t_suggestions[$id_item]['nb'] = $nb;
		$t_suggestions[$id_item]['items_actifs'] = '['.implode(',',$reactif_avec[$id_item]).']';
	}


	/* un tri sur le nombre puis on met tout à plat */
	$nb = $nom = $id_mol = $aFinal = array();
	
	foreach ($t_suggestions as $key => $row) {
	    $nb[$key]  = $row['nb'];
	    $nom[$key] = $row['nom'];
	    $id_mol[$key] = $row['id_mol'];
	}
  $item = array_map('strtolower', $nom);

	array_multisort($nb, SORT_DESC, $item, SORT_ASC, $t_suggestions);
	
	if (_request('debug')) {var_dump($t_suggestions);die();}
	
	return json_encode($t_suggestions);
	

}
?>