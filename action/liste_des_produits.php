<?php

	if (!defined("_ECRIRE_INC_VERSION")) return;

	$chaine = strtr($_POST['query'], 'àâäåãáÂÄÀÅÃÁçÇéèêëÉÊËÈïîìíÏÎÌÍñÑöôóòõÓÔÖÒÕùûüúÜÛÙÚÿ','aaaaaaAAAAAAcCeeeeEEEEiiiiIIIInNoooooOOOOOuuuuUUUUy');

	$nb_elements_retournes = 10;
	$nb_elements_trouves = 0;
	
	$chaine = strtolower($chaine);
	
	session_start();
	if (is_array($_SESSION['produits_choisis']))
		$produits_deja_choisis = implode(",", $_SESSION['produits_choisis']);
	session_write_close();
	
	$sql = "select id_item, nom from tbl_items where interrogeable = 1 ";
	if ($produits_deja_choisis)	$sql .="and id_item NOT IN(".$produits_deja_choisis.")";
	$sql .= "	and ( nom_sans_accent like '".addslashes($chaine)."%'
		or nom like '".addslashes($_POST['query'])."%')
	 	limit 0,5";
	$q = spip_query($sql);
	
	$nb_elements_trouves = spip_num_rows($q);
	
	$res=array();

	while ($row = spip_fetch_array($q)) {$res[] = $row;}
	
	// On complète par une recherche plus large
	$sql = "select id_item, nom from tbl_items 
				where interrogeable = 1 ";
	if ($produits_deja_choisis)	$sql .="and id_item NOT IN(".$produits_deja_choisis.")";
	$sql .= "				and nom_sans_accent like '%".addslashes($chaine)."%' 
					and nom_sans_accent not in (
							select nom_sans_accent from tbl_items where nom_sans_accent like '".addslashes($chaine)."%'
						)
					limit 0,".(10 - $nb_elements_trouves);
	$q = spip_query($sql);
	
	if (spip_num_rows($q)) $res[] = array('id_item' => 0, 'nom' => ''); // Séparateur
	$nb_elements_trouves += spip_num_rows($q);
	
	while ($row = spip_fetch_array($q)) {$res[] = $row;}


	if (!$nb_elements_trouves) die('{produits:'.json_encode(array(array('id_item' => 0, 'nom' => _T('nothing_found')))).'}');
	else die('{produits:'.json_encode($res).'}');
	
