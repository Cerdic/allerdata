<?php

function noms_detailles($txt) {
		
  $tableau_produits = $final = array();
  
	if (is_numeric($_REQUEST['p1'])) $tableau_produits[1] = $_REQUEST['p1']; 
	if (is_numeric($_REQUEST['p2'])) $tableau_produits[2] = $_REQUEST['p2'];
	if (is_numeric($_REQUEST['p3'])) $tableau_produits[3] = $_REQUEST['p3'];
	if (is_numeric($_REQUEST['p4'])) $tableau_produits[4] = $_REQUEST['p4'];
	if (is_numeric($_REQUEST['p5'])) $tableau_produits[5] = $_REQUEST['p5'];
	
	for ($i=1; $i<=5; $i++) {
    $q = "SELECT nom, source FROM tbl_items WHERE id_item = '".$tableau_produits[$i]."'";
    $res = spip_query($q);
    if ($p = spip_fetch_array($res))
      $final[] = array('index' => $i, 'nom' => $p['nom'], 'source' => $p['source']);
  }
  
  return(json_encode($final));

}
	
?>
