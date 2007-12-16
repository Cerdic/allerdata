<?php

function action_liste_des_produits() {
		
	$chaine = strtr(_request('query'), 'àâäåãáÂÄÀÅÃÁçÇéèêëÉÊËÈïîìíÏÎÌÍñÑöôóòõÓÔÖÒÕùûüúÜÛÙÚÿ','aaaaaaAAAAAAcCeeeeEEEEiiiiIIIInNoooooOOOOOuuuuUUUUy');
	$chaine = str_replace('œ','oe',$chaine);

	$nb_elements_retournes = 10;
	$nb_elements_trouves = 0;
	
	$chaine = strtolower($chaine);
	
	session_start();
	if (is_array($_SESSION['produits_choisis']))
		$produits_deja_choisis = implode(",", $_SESSION['produits_choisis']);
	session_write_close();
	
	$sql = "select id_item, nom, source, famille from tbl_items where id_type_item in (5,3) ";
	if ($produits_deja_choisis)	$sql .="and id_item NOT IN(".$produits_deja_choisis.")";
	$sql .= "	and ( nom_sans_accent like '".addslashes($chaine)."%'
		or source_sans_accent like '".addslashes($chaine)."%')";
	$q = spip_query($sql);
	
	$nb_elements_trouves = spip_num_rows($q);
	
	$res = $ids = array();

	$liste_noire = array();
	if (is_array($_SESSION['produits_choisis'])) $liste_noire = $_SESSION['produits_choisis'];
	while ($row = spip_fetch_array($q)) {$res[] = $row; $liste_noire[] = $row['id_item'];}
	
	// On complète par une recherche plus large (20 maxi)
	if ($nb_elements_trouves<20) {
		$sql = "select id_item, nom from tbl_items 
					where id_type_item in (5,3) ";
		if ($liste_noire)	$sql .="and id_item NOT IN(".implode(',',$liste_noire).")";
		$sql .= "	and ( nom_sans_accent like '%".addslashes($chaine)."%'
			or source_sans_accent like '%".addslashes($chaine)."%')
			LIMIT 0,".(20-$nb_elements_trouves);
		$q = spip_query($sql);

		$nb_elements_trouves += spip_num_rows($q);

		while ($row = spip_fetch_array($q)) {$res[] = $row;}
	}


	if (!$nb_elements_trouves) echo('{produits:'.json_encode(array(array('id_item' => 0, 'nom' => _T('nothing_found')))).'}');
	else echo('{produits:'.json_encode($res).'}');

}
	
?>