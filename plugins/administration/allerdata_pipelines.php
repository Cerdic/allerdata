<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Declaration des champs pour la recherche sur la tbl_items
 * utilisee dans le back office
 *
 * @param array $liste
 * @return array
 */
function allerdata_rechercher_liste_des_champs($liste){
	$liste['tbl_item'] = array('nom'=>4,'source'=>1,'famille'=>1);
	return $liste;
}


?>