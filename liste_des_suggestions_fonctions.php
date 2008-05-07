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
	
	if (isset($_REQUEST['p1']) && is_numeric($_REQUEST['p1'])) $tableau_produits[$_REQUEST['p1']] = $_REQUEST['p1']; 
	if (isset($_REQUEST['p2']) && is_numeric($_REQUEST['p2'])) $tableau_produits[$_REQUEST['p2']] = $_REQUEST['p2'];
	if (isset($_REQUEST['p3']) && is_numeric($_REQUEST['p3'])) $tableau_produits[$_REQUEST['p3']] = $_REQUEST['p3'];
	if (isset($_REQUEST['p4']) && is_numeric($_REQUEST['p4'])) $tableau_produits[$_REQUEST['p4']] = $_REQUEST['p4'];
	if (isset($_REQUEST['p5']) && is_numeric($_REQUEST['p5'])) $tableau_produits[$_REQUEST['p5']] = $_REQUEST['p5'];
	
  session_start();
  // Pour ne pas les reproposer ensuite il faut :
	// - Mémoriser les produits du penta
  // - Leurs sous-produits
	// - ainsi que leurs parents
  $_SESSION['produits_choisis'] = array();
	if ($tableau_produits) {
		$res = spip_query("
      select DISTINCT est_dans_id_item as id from tbl_est_dans where id_item IN (".implode(',',$tableau_produits).")
      UNION
      select DISTINCT id_item as id from tbl_est_dans where est_dans_id_item IN (".implode(',',$tableau_produits).")
    ");
		while ($row = spip_fetch_array($res)){
			$_SESSION['produits_choisis'][$row['id']] = $row['id'];
		}
	}
  session_write_close();

	if (!sizeof($tableau_produits)) return '[]';
	
	$produits = implode(",", $_SESSION['produits_choisis']);
  
	$tt = '';
	
	/* Liste des suggestions */
	$query = <<<EOQ
# Requête pour trouver les items à suggérer

(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.source, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit1)
            # Ne prendre que les éléments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit2 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous intéresse)
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
            # La cible est contenu dans un produit de type "produit" ou "espèce" (c'est ce dernier qui nous intéresse)
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
            # Idem que la première condition, mais on est dans une relation inverse cible vers source
    )
)	

UNION

(SELECT DISTINCT tbl_est_dans.est_dans_id_item AS id_item_penta, tbl_items_3.id_item, tbl_items_3.nom, tbl_items_3.source, tbl_items_3.nom_court
    FROM (tbl_est_dans INNER JOIN tbl_reactions_croisees ON tbl_est_dans.id_item = tbl_reactions_croisees.id_produit2)
            # Ne prendre que les éléments des est_dans qui ont un lien avec tbl_reactions_croisees en source
    INNER JOIN tbl_est_dans AS tbl_est_dans_1 ON tbl_reactions_croisees.id_produit1 = tbl_est_dans_1.id_item
            # Le produit cible de la RC est aussi en "contenu" du tbl_est_dans(1) (c'est le contenant qui nous intéresse)
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
            # La cible est contenu dans un produit de type "produit" ou "espèce" (c'est ce dernier qui nous intéresse)
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
            # Idem que la première condition, mais on est dans une relation inverse cible vers source
            # Il faut prévoir les réactions croisées qui de sont QUE dans le sens inverse
    )
)	


EOQ;
	
  $md5_query = md5($query);
  
  // tri 
  $q = spip_query("select resultat_json from cache_requetes where hash='".mysql_real_escape_string($md5_query)."' and page='liste_des_suggestions'");
  if (spip_num_rows($q)) {
    $r = spip_fetch_array($q);
    return $r['resultat_json'];
  }
  sort($tableau_produits);
  $signature = implode(',', $tableau_produits);
  
 	$res = spip_query($query);
	
	while ($row = spip_fetch_array($res)){
		if (!isset($t_suggestions[$row['id_item']])) {
			$t_suggestions[$row['id_item']] = array(
					'nom' => (($row['nom_court']=='')?$row['nom']:$row['nom_court']),
					'source' => $row['source'],
					'id_mol' => $row['id_item']);
		}
    if ($tableau_produits[$row['id_item_penta']]) {
      $reactif_avec[$row['id_item']][$row['id_item_penta']] = $row['id_item_penta'];
    }
	}
	
	foreach($t_suggestions as $id_item => $sugg) {
		$nb = sizeof($reactif_avec[$id_item]);
    if ($nb) {
  		$t_suggestions[$id_item]['nb'] = $nb;
  		$t_suggestions[$id_item]['items_actifs'] = '['.implode(',',$reactif_avec[$id_item]).']';
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
	
	if (_request('debug')) {var_dump($t_suggestions);die();}
	
  $suggestion = json_encode($t_suggestions);
  
  // On stocke pour un prochain appel
  spip_query("INSERT INTO cache_requetes (page,tuple,hash,resultat_json,date_maj) 
              VALUES('liste_des_suggestions',
                     '".mysql_real_escape_string($signature)."',
                     '".mysql_real_escape_string($md5_query)."',
                     '".mysql_real_escape_string($suggestion)."',
                     NOW())");

	return $suggestion;
	

}
?>